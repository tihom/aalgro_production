<?php
	require_once('/var/www/pricing/PHPExcelReader/Classes/PHPExcel.php');
	require_once('/var/www/pricing/PHPMailer/PHPMailerAutoload.php');
	require_once('/var/www/pricing/clientsMailSettings.php');
	
	class clientExcel
	{
		private $db_ops;
		private $excelOb;
		protected $transactionStatus = 0;
		private $history = array();

		public function __construct($db_ops) {
			$this->db_ops = $db_ops;
			$this->excelOb = new PhpExcel();
		}

		public function mailExcel($clientId, $allItems) {
			$excelFile = $this->getExcel($clientId, $allItems);
			$query = "SELECT client_name, client_email FROM clients WHERE client_id = " . $clientId;
			$result = $this->db_ops->getResults($query);

			$fp = fopen("mail.txt", "r");
			$content = fread($fp, filesize('mail.txt'));
			fclose($fp);

			$content = preg_replace("/\r\n/", "<br />", $content);

			$mail = new PHPMailer;

			$mail->isSMTP();
			$mail->Host = 'smtp.gmail.com';
			$mail->SMTPAuth = true;
			$mail->Username = EmailID;
			$mail->Password = EmailPassword;
			$mail->SMTPSecure = 'tls';

			$mail->From = 'anupam@aalgro.com';
			$mail->FromName = 'Aalgro';
			$mailReplyTo = strlen(EmailReplyTo)>0?EmailReplyTo:EmailID;
			$mail->addReplyTo($mailReplyTo);
			$mail->addAddress($result[0]['client_email'], $result[0]['client_name']);
			$mail->addCC(EmailCC);
			
			$mail->addAttachment('excels/clients/' . $excelFile);
			$mail->isHTML(true);

			$mail->Subject = 'Aalgro Prices';
			$mail->Body    = "Hi " . $result[0]['client_name'] . "<br /><br />" . $content;

			if(!$mail->send()) {
				$error = 1;
				$data = $mail->ErrorInfo;
			}
			else {
				$error = 0;
				$data = "EMail has been sent.";	
			}
		
			if($error == 0) {
				$this->updateHistory($clientId);
			}

			return json_encode(array('error' => $error, 'data' => $data));
		}

		public function getExcel($clientId, $allItems) {
			$excelData = $this->generateData($clientId, $allItems);
			$excelSheet = $this->generateExcel($clientId, $excelData);
			return $excelSheet;
		}

		private function generateData($clientId, $allItems) {
			$items = array();
			$itemsVarieties = array();
			$units = array();
			$itemsPrice = array();
			$excelData = array();
			$pricingHistory = array();

			foreach($allItems as $key => $value) {
				$items[$key] = $value['item_name'];
				foreach($value['item_varieties'] as $varietyId => $varietyName) {
					$itemsVarieties[$key][$varietyId] = $varietyName;
				}
			}

			$query = "SELECT * FROM units WHERE is_hidden = 0";
			$result = $this->db_ops->getResults($query);

			for($i=0; $i<sizeof($result); $i++) {
				$units[$result[$i]['unit_id']] = $result[$i]['unit_name'];
			}

			$query = "SELECT * FROM items_varieties_price";
			$result = $this->db_ops->getResults($query);

			for($i=0; $i<sizeof($result); $i++) {
				$itemsPrice[$result[$i]['item_id']][$result[$i]['item_variety_id']][$result[$i]['unit_id']][$result[$i]['price_category_id']] = $result[$i]['item_price'];
			}

			$query = "SELECT * FROM clients_items WHERE client_id = " . $clientId . " AND is_hidden = 0";
			$result = $this->db_ops->getResults($query);

			for($i=0; $i<sizeof($result); $i++) {
				$excelData[$i]['item_name'] = $items[$result[$i]['item_id']];
				$excelData[$i]['item_variety_name'] = $itemsVarieties[$result[$i]['item_id']][$result[$i]['item_variety_id']];
				$excelData[$i]['unit'] = $units[$result[$i]['unit_id']];

				@$price = $itemsPrice[$result[$i]['item_id']][$result[$i]['item_variety_id']][$result[$i]['unit_id']][$result[$i]['price_category_id']];
				
				$price -= ($result[$i]['discount']/100) * $price;
				$excelData[$i]['price'] = round($price, 1);
				$pricingHistory[$i]['item_id'] = $result[$i]['item_id'];
				$pricingHistory[$i]['item_variety_id'] = $result[$i]['item_variety_id'];
				$pricingHistory[$i]['unit_id'] = $result[$i]['unit_id'];
				$pricingHistory[$i]['price_category_id'] = $result[$i]['price_category_id'];
				$pricingHistory[$i]['discount'] = $result[$i]['discount'];
				$pricingHistory[$i]['item_price'] = round($price, 1);
				$pricingHistory[$i]['updated_at'] = time();
			}
			$this->history = $pricingHistory;
			return $excelData;
		}

		private function generateExcel($clientId, $excelData) {
			$size = sizeof($excelData);

			$sheetOb = $this->excelOb->setActiveSheetIndex(0);
			$sheetOb->setTitle('Aalgro Prices');

			$sheetOb->setCellValue('A1', 'Item Name');
			$sheetOb->setCellValue('B1', 'Item Variety');
			$sheetOb->setCellValue('C1', 'Unit');
			$sheetOb->setCellValue('D1', 'Price');

			$sheetOb->getStyle('A1:D1')->getFont()->setBold(true)->setSize(12);
			$sheetOb->getStyle('A1:D1')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
			$sheetOb->getStyle('D1:D'.($size+1))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
			$sheetOb->getStyle('A' . ($size+1) . ':D' . ($size+1))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
			$sheetOb->getColumnDimension('A')->setAutoSize(true);
			$sheetOb->getColumnDimension('B')->setAutoSize(true);
			$sheetOb->getColumnDimension('C')->setAutoSize(true);
			$sheetOb->getColumnDimension('D')->setAutoSize(true);

			$col = 2;
			for($i=0; $i<$size; $i++) {
				$sheetOb->setCellValue('A'.$col, $excelData[$i]['item_name']);
				$sheetOb->setCellValue('B'.$col, $excelData[$i]['item_variety_name']);
				$sheetOb->setCellValue('C'.$col, $excelData[$i]['unit']);
				$sheetOb->setCellValue('D'.$col, $excelData[$i]['price']);
				if($col%2==0) {
					$sheetOb->getStyle('A' . $col . ':D' . $col)->applyFromArray(
																array(
																	'fill' => array(
																					'type' => PHPExcel_Style_Fill::FILL_SOLID, 
																					'color' => array('rgb' => 'D3D3D3')
																					)
																	)
															);
				}
				$col++;
			}

			$objWriter = PHPExcel_IOFactory::createWriter($this->excelOb, "Excel2007");
			$name = date("Ymd").'_' . $clientId . '.xlsx';
			$objWriter->save('excels/clients/' . $name);
			return $name;
		}

		private function updateHistory($clientId) {
			$query = "INSERT INTO clients_items_price_history (client_id, item_id, item_variety_id, unit_id, price_category_id, discount, item_price, updated_at) VALUES";
			$queryParams = '';
			for($i=0; $i<sizeof($this->history); $i++) {
				$queryParams .= "(" . $clientId . ", " . $this->history[$i]['item_id'] . ", " . $this->history[$i]['item_variety_id'] . ", " . $this->history[$i]['unit_id'] . ", " . $this->history[$i]['price_category_id'] . ", " . $this->history[$i]['discount'] . ", " . $this->history[$i]['item_price'] . ", " . $this->history[$i]['updated_at'] . ")";
				if(($i+1)<sizeof($this->history)) {
					$queryParams .= ", ";
				}
			}

			$result = $this->db_ops->executeQuery($query . $queryParams);
			return $result;
		}
	}
?>
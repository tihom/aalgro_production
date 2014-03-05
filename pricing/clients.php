<?php
	require_once('db.php');
	class clients
	{
		private $db_ops;
		protected $transactionStatus = 0;

		public function __construct($db_ops) {
			$this->db_ops = $db_ops;
		}

		public function getClientsByCategory($categoryId) {
			$clients = array();
			
			$query = "SELECT * FROM clients WHERE client_category_id = " . $categoryId . " AND is_hidden = 0";
			$result = $this->db_ops->getResults($query);

			for($i=0; $i<sizeof($result); $i++) {
				$clients[] = array('client_id' => $result[$i]['client_id'], 'client_name' => $result[$i]['client_name']);
			}

			return array('clients' => $clients, 'category_id' => $categoryId);
		}

		public function getClientInfo($clientId) {
			$query = "SELECT * FROM clients WHERE client_id = " . $clientId;
			$result = $this->db_ops->getResults($query);

			$clientInfo = array();

			$clientInfo['client_id'] = $clientId;
			$clientInfo['client_name'] = $result[0]['client_name'];
			$clientInfo['client_phone'] = $result[0]['client_phone'];
			$clientInfo['client_address'] = $result[0]['client_address'];
			$clientInfo['client_email'] = $result[0]['client_email'];

			return json_encode(array('error' => 0, 'data' => $clientInfo));
		}

		public function editClientInfo($clientInfo) {
			$queryParams = array();

			$clientId = $clientInfo['client_id'];
			unset($clientInfo['client_id']);

			foreach($clientInfo as $key => $value) {
				$queryParams[] = $key . " = '" . $value . "'";
			}

			$updateString = implode(',', $queryParams);
			$query = "UPDATE clients SET " . $updateString . " WHERE client_id = " . $clientId;
			$result = $this->db_ops->executeQuery($query);

			if($result == 1) {
				$error = 0;
				$clientInfo['client_id'] = $clientId;
				$data = $clientInfo;
			}
			else {
				$error = 1;
				$data = '';
			}

			return json_encode(array('error' => $error, 'data' => $data));

		}

		public function deleteClient($clientId) {
			$query = "UPDATE clients SET is_hidden = 1 WHERE client_id = " . $clientId;
			$result = $this->db_ops->executeQuery($query);

			if($result == 1)
				$error = 0;
			else
				$error = 1;

			return $error;
		}

		public function addClient($client, $categoryId) {
			$name = NULL;
			$phone = NULL;
			$address = NULL;
			$category = NULL;

			$returnInfo;

			$name = $client['client_name'];
			$phone = $client['phone'];
			$address = $client['address'];
			$email = $client['email'];

			$query = "INSERT INTO clients (client_category_id, client_name, client_phone, client_address, client_email, client_added_at, is_hidden) VALUES (" . $categoryId . ", '" . $name . "', '" . $phone . "', '" . $address . "', '" . $email . "', " . time() . ", 0)";
			$result = $this->db_ops->executeQuery($query);

			if($result) {
				$lastId = $this->db_ops->getLastId();
				if($client['items_option'] == 'existing') {
					$res = $this->addClientItemsFromClients($lastId, $client['client_id'], $client['import_type']);
				}
				$returnInfo = array('client_id' => $lastId, 'client_name' => $name);
			}
			

			return json_encode(array('error' => 0, 'data' => $returnInfo));

		}

		public function getClientItems($clientId) {
			$itemsPrice = array();
			$client_items = array();
			$tempArray = array();

			$query = "SELECT * FROM items_varieties_price";
			$result = $this->db_ops->getResults($query);

			for($i=0; $i<sizeof($result); $i++) {
				$itemsPrice[$result[$i]['item_id']][$result[$i]['item_variety_id']][$result[$i]['unit_id']][$result[$i]['price_category_id']] = $result[$i]['item_price'];
			}

			$query = "SELECT * FROM clients_items WHERE is_hidden = 0 AND client_id = " . $clientId;
			$result = $this->db_ops->getResults($query);

			for($i=0; $i<sizeof($result); $i++) {
				if(isset($itemsPrice[$result[$i]['item_id']][$result[$i]['item_variety_id']][$result[$i]['unit_id']][$result[$i]['price_category_id']]))
					$item_price = $itemsPrice[$result[$i]['item_id']][$result[$i]['item_variety_id']][$result[$i]['unit_id']][$result[$i]['price_category_id']];
				else
					$item_price = 0;

				$tempArray[$result[$i]['unit_id']] = array('item_price' => $item_price, 'price_category_id' => $result[$i]['price_category_id'], 'discount' => $result[$i]['discount'], 'price_ts' => $result[$i]['updated_at']);
				$client_items[$result[$i]['item_id']][$result[$i]['item_variety_id']] = $tempArray;
				unset($tempArray);
			}

			return $client_items;

		}

		public function deleteClientItem($clientId, $clientItems) {
			$item_id = $clientItems['item_id'];
			$unit_id = $clientItems['unit_id'];
			$item_variety_id = $clientItems['item_variety_id'];
			$price_category_id = $clientItems['price_category_id'];

			$query = "UPDATE clients_items SET is_hidden = 1 WHERE item_id = " . $item_id . " AND unit_id = " . $unit_id . " AND item_variety_id = " . $item_variety_id . " AND price_category_id = " . $price_category_id . " AND client_id = " . $clientId;
			$result = $this->db_ops->executeQuery($query);

			if($result == 1)
				$error = 0;
			else
				$error = 1;

			return json_encode(array('error' => $error));
		}

		public function addClientItems($clientId, $clientItems) {
			$addedItems = array();
			$itemsPrice = array();
			$updated_at = time();

			$query = "SELECT * FROM items_varieties_price";
			$result = $this->db_ops->getResults($query);

			for($i=0; $i<sizeof($result); $i++) {
				$itemsPrice[$result[$i]['item_id']][$result[$i]['item_variety_id']][$result[$i]['unit_id']][$result[$i]['price_category_id']] = $result[$i]['item_price'];
			}

			$query = "INSERT INTO clients_items (client_id, item_id, item_variety_id, unit_id, price_category_id, discount, updated_at, is_hidden) VALUES ";
			
			for($i=0; $i<sizeof($clientItems); $i++) {
				$queryString = '';
				$queryString .= "(" . $clientId . ", " . $clientItems[$i]['item_id'] . ", " . $clientItems[$i]['item_variety_id'] . ", " . $clientItems[$i]['unit_id'] . ", " . $clientItems[$i]['price_category_id'] . ", " . $clientItems[$i]['discount'] . ", " . $updated_at . ", 0)";
				$result = $this->db_ops->executeQuery($query . $queryString);

				if($result == 1) {
					if(isset($itemsPrice[$clientItems[$i]['item_id']][$clientItems[$i]['item_variety_id']][$clientItems[$i]['unit_id']][$clientItems[$i]['price_category_id']]))
						$item_price = $itemsPrice[$clientItems[$i]['item_id']][$clientItems[$i]['item_variety_id']][$clientItems[$i]['unit_id']][$clientItems[$i]['price_category_id']];
					else
						$item_price = 0;
					$queryStringHistory = "INSERT INTO clients_items_price_history (client_id, item_id, item_variety_id, unit_id, price_category_id, discount, item_price, updated_at) VALUES (" . $clientId . ", " . $clientItems[$i]['item_id'] . ", " . $clientItems[$i]['item_variety_id'] . ", " . $clientItems[$i]['unit_id'] . ", " . $clientItems[$i]['price_category_id'] . ", " . $clientItems[$i]['discount'] . ", " . $item_price . ", " . $updated_at . ")";
					$this->db_ops->executeQuery($queryStringHistory);
					$addedItems[] = array('item_id' => $clientItems[$i]['item_id'], 'item_variety_id' => $clientItems[$i]['item_variety_id'], 'unit_id' => $clientItems[$i]['unit_id'], 'item_price' => $item_price, 'price_category_id' => $clientItems[$i]['price_category_id'], 'discount' => $clientItems[$i]['discount'], 'price_ts' => $updated_at);
				}
			}

			return json_encode(array('error' => 0, 'data' => $addedItems));
		}

		public function addClientItemsFromClients($newClientId, $existingClientId, $importType) {
			$query = "SELECT * FROM clients_items WHERE client_id = " . $existingClientId;
			$result = $this->db_ops->getResults($query);
			
			$query = "INSERT INTO clients_items (client_id, item_id, item_variety_id, unit_id, price_category_id, discount, updated_at, is_hidden) VALUES ";
			$queryString = '';

			switch($importType) {
				case 1:
					for($i=0; $i<sizeof($result); $i++) {
						$time = time();
						$queryString .= "(" . $newClientId . ", " . $result[$i]['item_id'] . ", " . $result[$i]['item_variety_id'] . ", " . $result[$i]['unit_id'] . ", " . $result[$i]['price_category_id'] . ", 0, " . $time . ", 0)";

						if(($i+1) < sizeof($result))
							$queryString .= ", ";
					}
					
					break;

				case 2:
					for($i=0; $i<sizeof($result); $i++) {
						$time = time();
						$queryString .= "(" . $newClientId . ", " . $result[$i]['item_id'] . ", " . $result[$i]['item_variety_id'] . ", " . $result[$i]['unit_id'] . ", " . $result[$i]['price_category_id'] . ", " . $result[$i]['discount'] . ", " . $time . ", 0)";

						if(($i+1) < sizeof($result))
							$queryString .= ", ";
					}

					break;
			}

			$result = $this->db_ops->executeQuery($query . $queryString);

			return $result;
		}

		public function getPriceCategories() {
			$priceCategories = array();

			$query = "SELECT * FROM price_categories WHERE is_hidden = 0";
			$result = $this->db_ops->getResults($query);

			for($i=0; $i<sizeof($result); $i++) {
				$priceCategories[$result[$i]['price_category_id']] = $result[$i]['price_category_name'];
			}

			return $priceCategories;
		}

		public function editClientItems($itemData, $clientId) {
			$itemsPrice = array();
			$responseArray = array();

			$query = "SELECT * FROM items_varieties_price";
			$result = $this->db_ops->getResults($query);

			for($i=0; $i<sizeof($result); $i++) {
				$itemsPrice[$result[$i]['item_id']][$result[$i]['item_variety_id']][$result[$i]['unit_id']][$result[$i]['price_category_id']] = $result[$i]['item_price'];
			}

			$query = "UPDATE clients_items SET ";

			$updated_at = time();

			for($i=0; $i<sizeof($itemData); $i++) {
				$queryString = "discount = " . $itemData[$i]['discount'] . ", updated_at = " . $updated_at . " WHERE client_id = " . $clientId . " AND item_id = " . $itemData[$i]['item_id'] . " AND item_variety_id = " . $itemData[$i]['item_variety_id'] . " AND unit_id = " . $itemData[$i]['unit_id'] . " AND price_category_id = " . $itemData[$i]['price_category_id'];
				$result = $this->db_ops->executeQuery($query . $queryString);
				if($result == 1) {
					if(isset($itemsPrice[$itemData[$i]['item_id']][$itemData[$i]['item_variety_id']][$itemData[$i]['unit_id']][$itemData[$i]['price_category_id']]))
						$item_price = $itemsPrice[$itemData[$i]['item_id']][$itemData[$i]['item_variety_id']][$itemData[$i]['unit_id']][$itemData[$i]['price_category_id']];
					else
						$item_price = 0;

					//$updateQuery = "INSERT INTO clients_items_price_history (client_id, item_id, item_variety_id, unit_id, price_category_id, discount, item_price, updated_at) VALUES (" . $clientId . ", " . $itemData[$i]['item_id'] . ", " . $itemData[$i]['item_variety_id'] . ", " . $itemData[$i]['unit_id'] . ", " . $itemData[$i]['price_category_id'] . ", " . $itemData[$i]['discount'] . ", " . $item_price . ", " . $updated_at . ")";
					//$this->db_ops->executeQuery($updateQuery);
				}
				else
					unset($itemData[$i]);
			}

			return $itemData;
		}
	}
?>
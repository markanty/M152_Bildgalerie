<?php
class DB{
	private $dbHost     = "localhost";
	private $dbUsername = "root";
	private $dbPassword = "";
	private $dbName     = "bildgalerie";
	
	public function __construct(){
		if(!isset($this->db)){
			// Verbindung zur Datenbank herstellen
			$conn = new mysqli($this->dbHost, $this->dbUsername, $this->dbPassword, $this->dbName);
			if($conn->connect_error){
				die("Fehler bei der Verbindung mit MySQL: " . $conn->connect_error);
			}else{
				$this->db = $conn;
			}
		}
	}
    
	/*
	 * Entnimmt den if/else Konditionen entsprechend Werte aus der Datenbank
	 * @param string name der Datenbank
	 * @param array select, where, order_by, limit und return_type sind Konditionen ($conditions)
	 */
	public function getRows($table, $conditions = array()){
		$sql = 'SELECT ';
		$sql .= array_key_exists("select",$conditions)?$conditions['select']:'*';
		$sql .= ' FROM '.$table;
		if(array_key_exists("where",$conditions)){
			$sql .= ' WHERE ';
			$i = 0;
			foreach($conditions['where'] as $key => $value){
				$pre = ($i > 0)?' AND ':'';
				$sql .= $pre.$key." = '".$value."'";
				$i++;
			}
		}
		
		if(array_key_exists("order_by",$conditions)){
			$sql .= ' ORDER BY '.$conditions['order_by']; 
		}else{
			$sql .= ' ORDER BY id DESC '; 
		}
		
		if(array_key_exists("start",$conditions) && array_key_exists("limit",$conditions)){
			$sql .= ' LIMIT '.$conditions['start'].','.$conditions['limit']; 
		}elseif(!array_key_exists("start",$conditions) && array_key_exists("limit",$conditions)){
			$sql .= ' LIMIT '.$conditions['limit']; 
		}
		
		$result = $this->db->query($sql);
		
		if(array_key_exists("return_type",$conditions) && $conditions['return_type'] != 'all'){
			switch($conditions['return_type']){
				case 'count':
					$data = $result->num_rows;
					break;
				case 'single':
					$data = $result->fetch_assoc();
					break;
				default:
					$data = '';
			}
		}else{
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$data[] = $row;
				}
			}
		}
		return !empty($data)?$data:false;
	}
	
	/*
	 * Fügt Daten in die Datenbank ein
	 * @param string name der Datenbank
	 * @param array Die (Bild-)Daten für die Datenbank
	 */
	public function insert($table, $data){
		if(!empty($data) && is_array($data)){
			$columns = '';
			$values  = '';
			$i = 0;
			if(!array_key_exists('created',$data)){
				$data['created'] = date("Y-m-d H:i:s");
			}
			if(!array_key_exists('modified',$data)){
				$data['modified'] = date("Y-m-d H:i:s");
			}
			foreach($data as $key=>$val){
				$pre = ($i > 0)?', ':'';
				$columns .= $pre.$key;
				$values  .= $pre."'".$this->db->real_escape_string($val)."'";
				$i++;
			}
			$query = "INSERT INTO ".$table." (".$columns.") VALUES (".$values.")";
			$insert = $this->db->query($query);
			return $insert?$this->db->insert_id:false;
		}else{
			return false;
		}
	}
	
	/*
	 * Updatet Daten in der Datenbank
	 * @param string name der Datenbank
	 * @param array Die geupdateten (Bild-)Daten für die Datenbank
	 * @param array where Konditionen für das Update
	 */
	public function update($table, $data, $conditions){
		if(!empty($data) && is_array($data)){
			$colvalSet = '';
			$whereSql = '';
			$i = 0;
			if(!array_key_exists('modified',$data)){
				$data['modified'] = date("Y-m-d H:i:s");
			}
			foreach($data as $key=>$val){
				$pre = ($i > 0)?', ':'';
				$colvalSet .= $pre.$key."='".$this->db->real_escape_string($val)."'";
				$i++;
			}
			if(!empty($conditions)&& is_array($conditions)){
				$whereSql .= ' WHERE ';
				$i = 0;
				foreach($conditions as $key => $value){
					$pre = ($i > 0)?' AND ':'';
					$whereSql .= $pre.$key." = '".$value."'";
					$i++;
				}
			}
			$query = "UPDATE ".$table." SET ".$colvalSet.$whereSql;
			$update = $this->db->query($query);
			return $update?$this->db->affected_rows:false;
		}else{
			return false;
		}
	}
	
	/*
	 * Löscht Daten aus der Datenbank
	 * @param string name der Datenbank
	 * @param array where Konditionen für das Löschen
	 */
	public function delete($table, $conditions){
		$whereSql = '';
		if(!empty($conditions)&& is_array($conditions)){
			$whereSql .= ' WHERE ';
			$i = 0;
			foreach($conditions as $key => $value){
				$pre = ($i > 0)?' AND ':'';
				$whereSql .= $pre.$key." = '".$value."'";
				$i++;
			}
		}
		$query = "DELETE FROM ".$table.$whereSql;
		$delete = $this->db->query($query);
		return $delete?true:false;
	}
}
<?php 
namespace Chrlb\PhpDao;

use Exception;
use PDO;

class PHPDAO{
  private $db_connection;
  private $prepared_statements = [];

  public function __construct(PDO $conn){
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $this->db_connection = $conn;
  }

  public function prepareStatement(string $query_tittle, string $sql_command, string $description = "NOT SET"){
    try{
      if($this->isPstmtExists($query_tittle)) {
        echo "prepared statement tittled:'$query_tittle' already exists.";
        return;
      };
      $required_params_count = substr_count($sql_command, '?');
      $pstmt = $this->db_connection->prepare($sql_command);
      $this->prepared_statements[$query_tittle] = ["sql"=>$pstmt, "description"=>$description, "params_count" => $required_params_count];

    }catch (Exception $e){
      echo $e->getMessage();
      return $e;
    }
  }

  public function replacePstmt(string $replacing_query_tittle, string $query_tittle, string $sql_command, string $description = "NOT SET"){
    try{
      if(!$this->isPstmtExists($replacing_query_tittle)) {
        echo "no prepared statement tittled:'$query_tittle'";
        return;
      };
      
      unset($this->prepared_statements[$replacing_query_tittle]);
      $required_params_count = substr_count($sql_command, '?');
      $pstmt = $this->db_connection->prepare($sql_command);
      $this->prepared_statements[$query_tittle] = ["sql"=>$pstmt, "description"=>$description, "params_count" => $required_params_count];
    }catch (Exception $e){
      echo $e->getMessage();
      return $e;
    }
  }

  public function executeQuery(string $query_tittle, array $params = []){
    try{
      $pstmt = $this->prepared_statements[$query_tittle]["sql"];
      $required_params_count = $this->pstmtParamsCount($query_tittle);
      $params_count = count($params);

      if(($params_count !== $required_params_count)){
        echo "Invalid parameter number: number of bound variables does not match number of tokensPDOException Object\n";
        return;
      }
      $success = $pstmt->execute($params); 

      if ($success) {
          $data = $pstmt->fetchAll(PDO::FETCH_ASSOC); 
          return $data;
      }

    }catch(Exception $e){
      echo $e->getMessage();
      return $e;
    }
  }

  public function getPstmtDescription(string $query_tittle): string{
    if(!$this->isPstmtExists( $query_tittle )) return "no prepared statement tittled:'$query_tittle'";
    return ($this->prepared_statements[$query_tittle]["description"]);
  }

  public function isPstmtExists(string $query_tittle): bool{
    return array_key_exists($query_tittle, $this->prepared_statements);
  }

  public function getAllPstmt(): array{
    return $this->prepared_statements;
  }

  public function pstmtParamsCount(string $query_tittle): int{
    if(!$this->isPstmtExists( $query_tittle )) {
      echo "no prepared statement tittled:'$query_tittle'";
      return -1;
    }
    return $this->prepared_statements[$query_tittle]["params_count"];
  }

}



?>
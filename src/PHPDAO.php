<?php 
namespace Chrlb\PhpDao;

use Exception;
use PDO;

class PHPDAO{
  private $db_connection;
  private $prepared_statements = [];

  public function __construct(PDO $conn){
    $this->db_connection = $conn;
  }

  public function prepareStatement(string $query_tittle, string $sql_command, string $description = "NOT SET"){
    try{
      if($this->isPstmtExist($query_tittle)) {
        
        return;
      };
      $pstmt = $this->db_connection->prepare($sql_command);
      $this->prepared_statements[$query_tittle] = [$pstmt,$description];

    }catch (Exception $e){
      echo $e->getMessage();
      return $e;
    }
  }

  public function getPstmtDescription(string $query_tittle): string{
    return ($this->prepared_statements[$query_tittle][1]);
  }

  public function isPstmtExist(string $query_tittle): bool{
    return array_key_exists($query_tittle,$this->prepared_statements);
  }

  public function getAllPstmt(): array{
    return $this->prepared_statements;
  }
}



?>
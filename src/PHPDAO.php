<?php 
namespace Chrlb\PhpDao;

use PDOException,InvalidArgumentException;
use OutOfBoundsException, RuntimeException;
use PDO;

class PHPDAO{
  protected $db_connection;
  protected $prepared_statements = [];

  public function __construct(PDO $conn){
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $this->db_connection = $conn;
  }

  protected function storeStatement(string $query_title, string $sql_command, string $description = "NOT SET"){
    $required_params_count = substr_count($sql_command, '?');
    $pstmt = $this->db_connection->prepare($sql_command);
    $this->prepared_statements[$query_title] = ["sql"=>$pstmt, "description"=>$description, "params_count" => $required_params_count];
  }

  protected function validateQueryParams(string $query_title, array $params): void{
    if(!$this->isPstmtExists( $query_title )) throw new OutOfBoundsException("no prepared statement titled:'$query_title'");
    
    $required_params_count = $this->pstmtParamsCount($query_title);
    $params_count = count($params);
    if($params_count !== $required_params_count) throw new InvalidArgumentException("Invalid parameter number: number of bound variables does not match number of tokens\n");
    
  }

  public function prepareStatement(string $query_title, string $sql_command, string $description = "NOT SET"):void{
    if($this->isPstmtExists($query_title)) throw new OutOfBoundsException("prepared statement titled:'$query_title' already exists.");
    
    try{
      $this->storeStatement( $query_title,  $sql_command,  $description);
    } catch (PDOException $e) {
      throw new InvalidArgumentException("Invalid SQL for statement '$query_title': " . $e->getMessage());
    }
  }

  public function replacePstmt(string $replacing_query_title, string $query_title, string $sql_command, string $description = "NOT SET"):void{
    if(!$this->isPstmtExists($replacing_query_title)) throw new OutOfBoundsException("no prepared statement titled:'$replacing_query_title'");
    
    try{
      unset($this->prepared_statements[$replacing_query_title]);
      $this->storeStatement( $query_title,  $sql_command,  $description);
    } catch (PDOException $e) {
      throw new InvalidArgumentException("Invalid SQL for statement '$query_title': " . $e->getMessage());
    }
  }

  public function executeQuery(string $query_title, array $params = []): array{

    $this->validateQueryParams($query_title, $params);
    $pstmt = $this->prepared_statements[$query_title]["sql"];

    try{
      $pstmt->execute($params); 
      return $pstmt->fetchAll(PDO::FETCH_ASSOC); 

    } catch (PDOException $e) {
      throw new RuntimeException("Failed to execute statement '$query_title': " . $e->getMessage());
    }
  }

  public function executeUpdate(string $query_title, array $params = []): int{
    
    $this->validateQueryParams($query_title, $params);
    $pstmt = $this->prepared_statements[$query_title]["sql"];
    
    try{
      $pstmt->execute($params); 
      return $pstmt->rowCount();

    } catch (PDOException $e) {
      throw new RuntimeException("Failed to execute statement '$query_title': " . $e->getMessage());
    }
  }

  public function getPstmtDescription(string $query_title): string{
    if(!$this->isPstmtExists( $query_title )) throw new OutOfBoundsException("no prepared statement titled:'$query_title'");
    return ($this->prepared_statements[$query_title]["description"]);
  }

  public function isPstmtExists(string $query_title): bool{
    return array_key_exists($query_title, $this->prepared_statements);
  }

  public function getAllPstmt(): array{
    return $this->prepared_statements;
  }

  public function pstmtParamsCount(string $query_title): int{
    if(!$this->isPstmtExists( $query_title )) throw new OutOfBoundsException("no prepared statement titled:'$query_title'");
    return $this->prepared_statements[$query_title]["params_count"];
  }

}

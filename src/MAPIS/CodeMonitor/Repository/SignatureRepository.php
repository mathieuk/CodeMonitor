<?php

namespace MAPIS\CodeMonitor\Repository;

use MAPIS\CodeMonitor\Entity\StatementSignature;

class SignatureRepository
{
	/**
	 * @var \PDO
	 */
	protected $db; 
	
	public function __construct(\PDO $db)
	{
		$this->db = $db;
	}

	protected function statementSignatureFromDbRecord($row)
	{
		$codeSig = new StatementSignature();
		$codeSig->setFqmn($row['fqmn']);
		$codeSig->setHash($row['hash']);
		$codeSig->setFile($row['file']);
		$codeSig->setCode($row['code']);

		return $codeSig;
	}

	public function store(StatementSignature $method)
	{
		$stmt = $this->db->prepare("REPLACE INTO statement_signature (fqmn, hash, file, code) VALUES (:fqmn, :hash, :file, :code)");
		$stmt->bindParam(':fqmn', $method->getFqmn());
		$stmt->bindParam(':hash', $method->getHash());
		$stmt->bindParam(':file', $method->getFile());
		$stmt->bindParam(':code', $method->getCode());

		$stmt->execute();
	}

	public function getWatchedMethods()
	{
		$result = $this->db->query("SELECT * FROM statement_signature");

		$methods = [];
		foreach ($result as $row)
		{
			$methods[] = $this->statementSignatureFromDbRecord($row);
		}
		
		return $methods;
	}
	
	public function delete(StatementSignature $statement)
	{
		$stmt = $this->db->prepare("DELETE FROM statement_signature WHERE fqmn = :fqmn");
		$stmt->bindParam(':fqmn', $statement->getFqmn());
		$stmt->execute();
	}
	
	public function findOneByFqmn($fqmn)
	{
		$stmt = $this->db->prepare("SELECT * FROM statement_signature WHERE fqmn = :fqmn");
		$stmt->bindParam(':fqmn', $fqmn);
		$result = $stmt->execute();

		$rows = $stmt->fetchAll();
		if ($result && count($rows))
		{
			return $this->statementSignatureFromDbRecord($rows[0]);
		}
		
		return NULL;
	}

}
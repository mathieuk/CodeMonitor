<?php

namespace MAPIS\CodeMonitor;

use MAPIS\CodeMonitor\Entity\StatementSignature;

class Finding
{
	protected $statementSig;
	protected $diff;

	public function __construct(StatementSignature $sig, $diff)
	{
		$this->statementSig = $sig;
		$this->diff = $diff;
	}
	/**
	 * @return StatementSignature
	 */
	public function getStatementSig ()
	{
		return $this->statementSig;
	}

	/**
	 * @return string
	 */
	public function getDiff ()
	{
		return $this->diff;
	}


}
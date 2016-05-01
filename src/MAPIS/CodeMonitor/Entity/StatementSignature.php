<?php

namespace MAPIS\CodeMonitor\Entity;

class StatementSignature
{
	protected $fqmn;
	protected $hash;
	protected $file;
	protected $code;

	/**
	 * @return string
	 */
	public function getFqmn ()
	{
		return $this->fqmn;
	}

	/**
	 * @param string $fqmn
	 */
	public function setFqmn ($fqmn)
	{
		$this->fqmn = $fqmn;
	}

	/**
	 * @return string
	 */
	public function getHash ()
	{
		return $this->hash;
	}

	/**
	 * @param string $hash
	 */
	public function setHash ($hash)
	{
		$this->hash = $hash;
	}

	/**
	 * @return string
	 */
	public function getFile ()
	{
		return $this->file;
	}

	/**
	 * @param string $file
	 */
	public function setFile ($file)
	{
		$this->file = $file;
	}

	/**
	 * @return mixed
	 */
	public function getCode ()
	{
		return $this->code;
	}

	/**
	 * @param mixed $code
	 */
	public function setCode ($code)
	{
		$this->code = $code;
	}




}
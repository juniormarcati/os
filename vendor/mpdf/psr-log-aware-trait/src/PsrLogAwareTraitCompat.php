<?php

namespace Mpdf\PsrLogAwareTrait;

trait PsrLogAwareTrait
{
	/**
	 * @var \Psr\Log\LoggerInterface|null
	 */
	protected $logger;

	public function setLogger(\Psr\Log\LoggerInterface $logger)
	{
		$this->logger = $logger;
	}
}

<?php

namespace Mpdf\PsrLogAwareTrait;

trait PsrLogAwareTrait
{
	/**
	 * @var \Psr\Log\LoggerInterface|null
	 */
	protected $logger;

	public function setLogger(\Psr\Log\LoggerInterface $logger): void
	{
		$this->logger = $logger;
	}
}

<?php

namespace Mpdf\PsrLogAwareTrait;

trait MpdfPsrLogAwareTrait
{
	/**
	 * @var \Psr\Log\LoggerInterface|null
	 */
	protected $logger;

	public function setLogger(\Psr\Log\LoggerInterface $logger)
	{
		$this->logger = $logger;

		if (property_exists($this, 'services') && is_array($this->services)) {
			foreach ($this->services as $name) {
				if ($this->$name && $this->$name instanceof \Psr\Log\LoggerAwareInterface) {
					$this->$name->setLogger($logger);
				}
			}
		}
	}
}

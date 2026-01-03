<?php

namespace Mpdf\PsrLogAwareTrait;

$hasVoidReturnType = false;

if (interface_exists(\Psr\Log\LoggerAwareInterface::class)) {
	try {
		$method = new \ReflectionMethod(\Psr\Log\LoggerAwareInterface::class, 'setLogger');
		$type = $method->getReturnType();
		$hasVoidReturnType = $type instanceof \ReflectionNamedType
			&& $type->getName() === 'void'
			&& !$type->allowsNull();
	} catch (\ReflectionException $e) {
		$hasVoidReturnType = false;
	}
}

if (!trait_exists(__NAMESPACE__ . '\\PsrLogAwareTrait', false)) {
	if ($hasVoidReturnType) {
		require __DIR__ . '/PsrLogAwareTraitVoid.php';
	} else {
		require __DIR__ . '/PsrLogAwareTraitCompat.php';
	}
}

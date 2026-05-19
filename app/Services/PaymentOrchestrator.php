<?php

namespace App\Services;

use App\Payment\Strategies\PaymentStrategyInterface;
use App\Payment\Strategies\QrRetrievalStrategy;
use App\Payment\Strategies\StateTransitionStrategy;
use App\Payment\Strategies\TransactionInitStrategy;

class PaymentOrchestrator
{
    private array $strategies = [];

    private array $context = [];

    public function __construct()
    {
        $this->registerDefaultStrategies();
    }

    public function registerStrategy(PaymentStrategyInterface $strategy): self
    {
        $this->strategies[$strategy->getName()] = $strategy;

        return $this;
    }

    public function setContext(array $context): self
    {
        $this->context = $context;

        return $this;
    }

    public function execute(string $strategyName): self
    {
        if (! isset($this->strategies[$strategyName])) {
            throw new \InvalidArgumentException("Estrategia desconocida: {$strategyName}");
        }

        $strategy = $this->strategies[$strategyName];

        if (! $strategy->canExecute($this->context)) {
            throw new \InvalidArgumentException("Contexto insuficiente para ejecutar: {$strategyName}");
        }

        $this->context = $strategy->execute($this->context);

        return $this;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function getQr(): self
    {
        return $this->execute('qr_retrieval');
    }

    public function initTransaction(): self
    {
        return $this->execute('transaction_init');
    }

    public function transitionTo(string $newState): self
    {
        $this->context['nuevo_estado'] = $newState;

        return $this->execute('state_transition');
    }

    private function registerDefaultStrategies(): void
    {
        $this->strategies['qr_retrieval'] = new QrRetrievalStrategy;
        $this->strategies['transaction_init'] = new TransactionInitStrategy;
        $this->strategies['state_transition'] = new StateTransitionStrategy;
    }
}

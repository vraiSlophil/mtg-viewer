<?php

namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiRequestLoggerSubscriber implements EventSubscriberInterface
{
    private const REQUEST_START_ATTRIBUTE = '_api_request_start';

    public function __construct(
        private readonly LoggerInterface $logger
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $path = $request->getPathInfo();

        if (!str_starts_with($path, '/api/')) {
            return;
        }

        $request->attributes->set(self::REQUEST_START_ATTRIBUTE, microtime(true));

        $this->logger->info('API request started', [
            'method' => $request->getMethod(),
            'path' => $path,
            'query' => $request->query->all(),
        ]);
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $path = $request->getPathInfo();

        if (!str_starts_with($path, '/api/')) {
            return;
        }

        $startedAt = $request->attributes->get(self::REQUEST_START_ATTRIBUTE);
        $durationMs = is_float($startedAt)
            ? round((microtime(true) - $startedAt) * 1000, 2)
            : null;

        $this->logger->info('API request completed', [
            'method' => $request->getMethod(),
            'path' => $path,
            'status_code' => $event->getResponse()->getStatusCode(),
            'duration_ms' => $durationMs,
        ]);
    }
}

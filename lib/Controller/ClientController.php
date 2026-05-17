<?php
declare(strict_types=1);

namespace OCA\Athena\Controller;

use OCA\Athena\AppInfo\Application;
use OCA\Athena\Attribute\ClientTokenRequired;
use OCA\Athena\Exception\NotFoundException;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\PublicPage;
use OCP\AppFramework\Http\Response;
use OCP\AppFramework\Http\StreamResponse;
use OCP\IAppConfig;
use OCP\IRequest;
use Psr\Log\LoggerInterface;

class ClientController extends Controller {
    public function __construct(
        IRequest                        $request,
        private readonly IAppConfig     $appConfig,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct(Application::APP_ID, $request);
    }

    #[PublicPage]
    #[NoCSRFRequired]
    #[ClientTokenRequired]
    public function library(): Response {
        $path = $this->appConfig->getValueString(Application::APP_ID, 'library_path', '');

        if ($path === '' || !is_file($path)) {
            $this->logger->warning('Athena: library_path is not configured or file not found', ['path' => $path]);
            $response = new Response();
            $response->setStatus(Http::STATUS_NOT_FOUND);
            return $response;
        }

        $etag = hash('sha256', (string)filemtime($path) . ':' . filesize($path));

        $ifNoneMatch = trim($this->request->getHeader('If-None-Match'), '"');
        if ($ifNoneMatch === $etag) {
            $response = new Response();
            $response->setStatus(Http::STATUS_NOT_MODIFIED);
            $response->addHeader('ETag', '"' . $etag . '"');
            return $response;
        }

        $response = new StreamResponse($path);
        $response->addHeader('Content-Type', 'application/octet-stream');
        $response->addHeader('Content-Disposition', 'attachment; filename="libathena.so"');
        $response->addHeader('ETag', '"' . $etag . '"');
        $response->addHeader('Content-Length', (string)filesize($path));
        return $response;
    }
}

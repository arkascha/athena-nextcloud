<?php
declare(strict_types=1);

namespace OCA\Athena\Controller;

use OCA\Athena\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;

class PageController extends Controller {
    public function __construct(IRequest $request) {
        parent::__construct(Application::APP_ID, $request);
    }

    #[NoAdminRequired]
    public function index(): TemplateResponse {
        return new TemplateResponse(Application::APP_ID, 'index');
    }
}

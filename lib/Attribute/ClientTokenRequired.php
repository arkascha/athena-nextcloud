<?php
declare(strict_types=1);

namespace OCA\Athena\Attribute;

use Attribute;

/**
 * Mark a controller action as requiring a valid Kobo client Bearer token.
 * The ClientTokenMiddleware reads this attribute and enforces authentication.
 */
#[Attribute(Attribute::TARGET_METHOD)]
class ClientTokenRequired {
}

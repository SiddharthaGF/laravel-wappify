<?php

declare(strict_types=1);

namespace AiluraCode\Wappify;

use Netflie\WhatsAppCloudApi\WhatsAppCloudApi as WhatsAppCloudApiBase;

/**
 * Package transport: pure passthrough to the Netflie base.
 *
 * This class MUST NOT persist anything. Outbound persistence is owned
 * exclusively by the send commands in `src/Actions` (single-owner rule).
 */
final class WhatsAppCloudApi extends WhatsAppCloudApiBase {}

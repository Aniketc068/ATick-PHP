<?php

declare(strict_types=1);

namespace Aniketc068\ATick;

/**
 * Thrown by any ATick operation that fails. The message is the human-readable
 * reason reported by the engine (bad password, invalid PDF, network error, ...).
 */
final class AtickException extends \RuntimeException
{
}

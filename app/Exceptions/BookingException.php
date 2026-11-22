<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * A booking rule was broken (slot full, no stock, dose not due…).
 * The message is written for the person using the app and is shown to them.
 */
class BookingException extends RuntimeException {}

<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Grpc;

/**
 * the const inside this class is copied from this address.
 * @see https://github.com/grpc/grpc-java/blob/b363f80764bcc8452ef54511b6c6d6b596f5f177/api/src/main/java/io/grpc/Status.java
 * @see https://grpc.github.io/grpc/core/md_doc_statuscodes.html
 */
class StatusCode
{
    /**
     * The operation completed successfully.
     */
    public const OK = 0;

    /**
     * The operation was cancelled (typically by the caller).
     */
    public const CANCELLED = 1;

    /**
     * Unknown error.  An example of where this error may be returned is
     * if a Status value received from another address space belongs to
     * an error-space that is not known in this address space.  Also
     * errors raised by APIs that do not return enough error information
     * may be converted to this error.
     */
    public const UNKNOWN = 2;

    /**
     * Client specified an invalid argument.  Note that this differs
     * from FAILED_PRECONDITION.  INVALID_ARGUMENT indicates arguments
     * that are problematic regardless of the state of the system
     * (e.g., a malformed file name).
     */
    public const INVALID_ARGUMENT = 3;

    /**
     * Deadline expired before operation could complete.  For operations
     * that change the state of the system, this error may be returned
     * even if the operation has completed successfully.  For example, a
     * successful response from a server could have been delayed long
     * enough for the deadline to expire.
     */
    public const DEADLINE_EXCEEDED = 4;

    /**
     * Some requested entity (e.g., file or directory) was not found.
     */
    public const NOT_FOUND = 5;

    /**
     * Some entity that we attempted to create (e.g., file or directory) already exists.
     */
    public const ALREADY_EXISTS = 6;

    /**
     * The caller does not have permission to execute the specified
     * operation.  PERMISSION_DENIED must not be used for rejections
     * caused by exhausting some resource (use RESOURCE_EXHAUSTED
     * instead for those errors).  PERMISSION_DENIED must not be
     * used if the caller cannot be identified (use UNAUTHENTICATED
     * instead for those errors).
     */
    public const PERMISSION_DENIED = 7;

    /**
     * Some resource has been exhausted, perhaps a per-user quota, or
     * perhaps the entire file system is out of space.
     */
    public const RESOURCE_EXHAUSTED = 8;

    /**
     * Operation was rejected because the system is not in a state
     * required for the operation's execution.  For example, directory
     * to be deleted may be non-empty, an rmdir operation is applied to
     * a non-directory, etc.
     *
     * <p>A litmus test that may help a service implementor in deciding
     * between FAILED_PRECONDITION, ABORTED, and UNAVAILABLE:
     * (a) Use UNAVAILABLE if the client can retry just the failing call.
     * (b) Use ABORTED if the client should retry at a higher-level
     * (e.g., restarting a read-modify-write sequence).
     * (c) Use FAILED_PRECONDITION if the client should not retry until
     * the system state has been explicitly fixed.  E.g., if an "rmdir"
     * fails because the directory is non-empty, FAILED_PRECONDITION
     * should be returned since the client should not retry unless
     * they have first fixed up the directory by deleting files from it.
     */
    public const FAILED_PRECONDITION = 9;

    /**
     * The operation was aborted, typically due to a concurrency issue
     * like sequencer check failures, transaction aborts, etc.
     *
     * <p>See litmus test above for deciding between FAILED_PRECONDITION,
     * ABORTED, and UNAVAILABLE.
     */
    public const ABORTED = 10;

    /**
     * Operation was attempted past the valid range.  E.g., seeking or
     * reading past end of file.
     *
     * <p>Unlike INVALID_ARGUMENT, this error indicates a problem that may
     * be fixed if the system state changes. For example, a 32-bit file
     * system will generate INVALID_ARGUMENT if asked to read at an
     * offset that is not in the range [0,2^32-1], but it will generate
     * OUT_OF_RANGE if asked to read from an offset past the current
     * file size.
     *
     * <p>There is a fair bit of overlap between FAILED_PRECONDITION and OUT_OF_RANGE.
     * We recommend using OUT_OF_RANGE (the more specific error) when it applies
     * so that callers who are iterating through
     * a space can easily look for an OUT_OF_RANGE error to detect when they are done.
     */
    public const OUT_OF_RANGE = 11;

    /**
     * Operation is not implemented or not supported/enabled in this service.
     */
    public const UNIMPLEMENTED = 12;

    /**
     * Internal errors.  Means some invariants expected by underlying
     * system has been broken.  If you see one of these errors,
     * something is very broken.
     */
    public const INTERNAL = 13;

    /**
     * The service is currently unavailable.  This is a most likely a
     * transient condition and may be corrected by retrying with
     * a backoff. Note that it is not always safe to retry
     * non-idempotent operations.
     *
     * <p>See litmus test above for deciding between FAILED_PRECONDITION,
     * ABORTED, and UNAVAILABLE.
     */
    public const UNAVAILABLE = 14;

    /**
     * Unrecoverable data loss or corruption.
     */
    public const DATA_LOSS = 15;

    /**
     * The request does not have valid authentication credentials for the
     * operation.
     */
    public const UNAUTHENTICATED = 16;

    /**
     * @see https://grpc.github.io/grpc/core/md_doc_statuscodes.html
     */
    public const HTTP_CODE_MAPPING = [
        self::OK => 200,
        self::CANCELLED => 499,
        self::UNKNOWN => 500,
        self::INVALID_ARGUMENT => 400,
        self::DEADLINE_EXCEEDED => 504,
        self::NOT_FOUND => 404,
        self::ALREADY_EXISTS => 409,
        self::PERMISSION_DENIED => 403,
        self::RESOURCE_EXHAUSTED => 429,
        self::FAILED_PRECONDITION => 400,
        self::ABORTED => 409,
        self::OUT_OF_RANGE => 400,
        self::UNIMPLEMENTED => 501,
        self::INTERNAL => 500,
        self::UNAVAILABLE => 503,
        self::DATA_LOSS => 500,
        self::UNAUTHENTICATED => 401,
    ];
}

<?php

namespace App\General\DoctrineExtensions;

use Doctrine\Common\EventManager;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use MongoDB\Client;
use MongoDB\Driver\Exception\RuntimeException;
use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\Session;
use MongoDB\Driver\WriteConcern;

/**
 * Extends Doctrine2 MongoDB Object Document Mapper (ODM) library
 * to add support for sessions and multi-document transactions.
 *
 * @see https://zgadzaj.com/development/mongodb/mongodb-multi-document-transactions-in-symfony-4-with-doctrine-and-mongodb-odm-bundle
 * @see https://www.doctrine-project.org/projects/mongodb-odm.html
 * @see https://docs.mongodb.com/master/core/transactions/
 */
class TransactionalDocumentManager extends DocumentManager
{
    private ?Session $session = null;

    public function __construct(?Client $client = null, ?Configuration $config = null, ?EventManager $eventManager = null)
    {
        parent::__construct($client, $config, $eventManager);
    }

    /**
     * @see http://php.net/manual/en/mongodb-driver-session.starttransaction.php
     * @see https://docs.mongodb.com/manual/reference/method/Session.startTransaction/
     * @see https://docs.mongodb.com/master/core/transactions/
     * @see https://docs.mongodb.com/manual/reference/read-concern-snapshot/
     * @see https://docs.mongodb.com/manual/reference/write-concern/#writeconcern._dq_majority_dq_
     */
    public function startTransaction(array $options = []): void
    {
        $this->startSession();

        $this->session->startTransaction(array_merge([
            'readConcern' => new ReadConcern('snapshot'),
            'writeConcern' => new WriteConcern(WriteConcern::MAJORITY),
        ], $options));
    }

    /**
     * @see http://php.net/manual/en/mongodb-driver-session.committransaction.php
     * @see https://docs.mongodb.com/manual/reference/method/Session.commitTransaction/
     * @see https://docs.mongodb.com/manual/reference/command/commitTransaction/
     * @see https://docs.mongodb.com/master/core/transactions/
     */
    public function commitTransaction(): void
    {
        $this->session->commitTransaction();
    }

    /**
     * @see http://php.net/manual/en/mongodb-driver-session.aborttransaction.php
     * @see https://docs.mongodb.com/manual/reference/method/Session.abortTransaction/
     * @see https://docs.mongodb.com/manual/reference/command/abortTransaction/
     * @see https://docs.mongodb.com/master/core/transactions/
     */
    public function abortTransaction(): void
    {
        $this->session->abortTransaction();
    }

    /**
     * @see http://php.net/manual/en/mongodb-driver-manager.startsession.php
     * @see https://docs.mongodb.com/manual/reference/server-sessions/
     * @see https://docs.mongodb.com/manual/reference/method/Session/
     * @see https://docs.mongodb.com/manual/reference/read-preference/#primary
     */
    public function startSession(array $options = []): void
    {
        if (!$this->session) {
            $this->session = $this->getClient()->startSession(array_merge([
                'readPreference' => new ReadPreference(ReadPreference::RP_PRIMARY),
            ], $options));
        } else {
            throw new RuntimeException('Session already started.');
        }
    }

    /**
     * @see https://docs.mongodb.com/manual/reference/server-sessions/
     * @see https://docs.mongodb.com/manual/reference/method/Session/
     */
    public function getSession(): Session
    {
        return $this->session;
    }

    /**
     * @see http://php.net/manual/en/mongodb-driver-session.endsession.php
     * @see https://docs.mongodb.com/manual/reference/server-sessions/
     * @see https://docs.mongodb.com/manual/reference/method/Session/
     */
    public function endSession(): void
    {
        if ($this->session) {
            $this->session->endSession();
        } else {
            throw new RuntimeException('Session not found.');
        }
    }
}

<?php declare(strict_types=1);

namespace App\TenantBundle\Processor;

use Enqueue\Client\CommandSubscriberInterface;
use App\TenantBundle\Manager\TenantInstallManager;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Interop\Queue\Processor;
use Psr\Log\LogLevel;

/**
 * Class TenantBuildProcessor
 * @package App\TenantBundle\Processor
 */
class TenantBuildProcessor implements Processor, CommandSubscriberInterface
{
    const COMMAND = 'tenantInstall';

    /**
     * @var TenantInstallManager
     */
    private $installManager;


    /**
     * TenantBuildProcessor constructor.
     *
     * @param TenantInstallManager $installManager
     */
    public function __construct(
        TenantInstallManager $installManager
    ) {
        $this->installManager = $installManager;
    }

    /**
     * @param Message $message
     * @param Context $context
     *
     * @return object|string
     * @throws \Doctrine\ODM\MongoDB\DocumentNotFoundException
     */
    public function process(Message $message, Context $context)
    {
        $body = $message->getBody();
        $payload = json_decode($body, true);

        if(json_last_error() !== JSON_ERROR_NONE) {
            $this->log(LogLevel::ERROR, 'Unable to extract queue message', ['body' => $payload, 'type' => gettype($payload)]);

            return self::REJECT;
        }

        if (array_key_exists('uuid', $payload) === false || empty($payload['uuid'])) {
            return self::REJECT;
        }


        $uuid = $payload['uuid'];
        $this->installManager->process($uuid);

        return self::ACK;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedCommand()
    {
        return self::COMMAND;
    }
}
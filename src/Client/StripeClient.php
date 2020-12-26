<?php
/**
 * Created by PhpStorm.
 * User: odolinski
 * Date: 23/12/2020
 * Time: 16:30
 */

namespace App\Client;


use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Psy\Exception\ErrorException;
use Stripe\Charge;
use Stripe\Stripe;

class StripeClient
{
    private $config;
    private $em;
    private $logger;

    public function __construct($secretKey, array $config, EntityManagerInterface $em, LoggerInterface $logger)
    {
        Stripe::setApiKey($secretKey);
        $this->config = $config;
        $this->em = $em;
        $this->logger = $logger;
    }


    public function createPremiumCharge(User $user, $token)
    {
        try {
            $charge = Stripe::create([
                'amount' => $this->config['decimal'] ? $this->config['premium_amount'] * 100 : $this->config['premium_amount'],
                'currency' => $this->config['currency'],
                'description' => 'PicBak Plus',
                'source' => $token,
                'receipt_email' => $user->getEmail(),
            ]);
        } catch (ErrorException $e) {
            $this->logger->error(sprintf('%s exception encountered when creating a premium payment: "%s"', get_class($e), $e->getMessage()), ['exception' => $e]);

            throw $e;
        }

        $user->setChargeId($charge->id);
        $user->setPremium($charge->paid);
        $this->em->flush();
    }
}

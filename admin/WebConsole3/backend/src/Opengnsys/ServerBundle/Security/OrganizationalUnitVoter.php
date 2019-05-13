<?php

namespace Opengnsys\ServerBundle\Security;

use Opengnsys\ServerBundle\Entity\OrganizationalUnit;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityManager;

class OrganizationalUnitVoter extends Voter
{
    private $entityManager;
    private $session;


    public function __construct(EntityManager $entityManager, Session $session)
    {
        $this->entityManager = $entityManager;
        $this->session = $session;
    }

   protected function supports($attribute, $subject)
   {
       return $subject instanceof OrganizationalUnit && $attribute === 'ROLE_OPENGNSYS_SERVER_OU';
   }

   protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
   {
       $user = $token->getUser();

        return $this->checker($user, $attribute, $subject);

   }

   public function checker($user, $attribute, $subject){
       if (!$user instanceof \Opengnsys\CoreBundle\Entity\User) {
           return false;
       }

       if($user != null){
           foreach ($user->getOrganizationalUnits() as $ou){
               if($ou->getId() === $subject->getId()){
                   return true;
               }
           }
       }

       return false;
   }
}
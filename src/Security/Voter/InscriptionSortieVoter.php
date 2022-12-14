<?php

namespace App\Security\Voter;

use App\Entity\Sortie;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class InscriptionSortieVoter extends Voter
{
    public const INSCRIPTION = 'INSCRIPTION';
    public const DESISTEMENT = 'DESISTEMENT';
    public const MODIFIER = 'MODIFIER';
    public const ANNULER = 'ANNULER';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::INSCRIPTION, self::DESISTEMENT, self::MODIFIER, self::ANNULER])
            && $subject instanceof \App\Entity\Sortie;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        /**
         * @var Sortie $subject
         */
        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::INSCRIPTION:
                if(
                    (!$subject->getParticipant()->contains($user)) and
                    $subject->getEtat()->getLibelle() == "Ouverte" and
                    $subject->getNbInscriptionMax() != count( $subject->getParticipant() ) and
                    $subject->getDateLimiteInscription() >= new \DateTime()
                )
                {
                    return true;
                }
                else
                {
                    return false;
                }
            case self::DESISTEMENT:
                if(
                    $subject->getParticipant()->contains($user) and
                    (
                        $subject->getEtat()->getLibelle() == "Ouverte" or
                        $subject->getEtat()->getLibelle() == "Clôturée"
                    )
                )
                {
                    return true;
                }
                else
                {
                    return false;
                }
            case self::MODIFIER:
                if(
                    ($subject->getOrganisateur() == $user or
                    is_array($user->getRoles()) and in_array('ROLE_ADMIN', $user->getRoles()) or
                    !is_array($user->getRoles()) and $user->getRoles() == 'ROLE_ADMIN')
                    and
                    (
                        $subject->getEtat()->getLibelle() == "Créée" or
                        $subject->getEtat()->getLibelle() == "Ouverte" or
                        $subject->getEtat()->getLibelle() == "Clôturée"
                    )
                )
                {
                    return true;
                }
                else
                {
                    return false;
                }
            case self::ANNULER:
                if(
                    ($subject->getOrganisateur() == $user or
                        is_array($user->getRoles()) and in_array('ROLE_ADMIN', $user->getRoles()) or
                        !is_array($user->getRoles()) and $user->getRoles() == 'ROLE_ADMIN')
                    and
                    (
                        $subject->getEtat()->getLibelle() == "Créée" or
                        $subject->getEtat()->getLibelle() == "Ouverte" or
                        $subject->getEtat()->getLibelle() == "Clôturée"
                    )
                )
                {
                    return true;
                }
                else
                {
                    return false;
                }
        }

        return false;
    }
}

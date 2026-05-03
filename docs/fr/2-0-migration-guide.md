# Guide de Migration vers 2.0

Authorization 2.0 apporte de nouvelles fonctionnalités ainsi que quelques
ruptures de compatibilité.

## Ruptures de Compatibilité

`IdentityInterface` a reçu des typehints. Si votre application implémente cette
interface, vous devez mettre à jour vos signatures.

`IdentityInterface` ajoute aussi `canResult()`, qui renvoie toujours un
`ResultInterface`, tandis que `can()` renvoie désormais toujours un booléen.

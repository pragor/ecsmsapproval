# EC SMS Approval

Module PrestaShop 1.7 — *Ether Creation*

Gestion du consentement SMS des clients : recueil du consentement au checkout, stockage par boutique, consultation back-office et export CSV.

---

## Fonctionnalités

- **Case à cocher au checkout** : une checkbox opt-in est affichée à l'étape livraison (après le choix du transporteur). Le choix est enregistré en base via AJAX à chaque changement.
- **Stockage par client et par boutique** : le consentement (approuvé / refusé / aucune réponse) est conservé en base, avec les dates de création et de mise à jour.
- **Badge dans la fiche client** : un badge coloré (vert / rouge / gris) est affiché dans la fiche client back-office pour visualiser le statut SMS en un coup d'œil.
- **Liste d'administration** : un menu dédié (`SMS Approval`) liste tous les consentements avec pagination (50 par page), compatible multiboutique.
- **Export CSV** : export de tous les consentements au format CSV (séparateur `;`, BOM UTF-8 pour compatibilité Excel), avec nom, email, statut invité, shop, consentement et dates.
- **Hook inter-modules** : expose le hook `actionEcSmsApprovalCheck` pour que d'autres modules puissent interroger le consentement d'un client sans dépendance directe. Retourne `['has_record' => bool, 'approved' => bool|null]`.

## Hooks utilisés

| Hook | Rôle |
|------|------|
| `displayAfterCarrier` | Affiche la checkbox de consentement à l'étape livraison |
| `displayAdminCustomers` | Affiche le badge statut SMS dans la fiche client |
| `actionFrontControllerSetMedia` | Charge JS/CSS front sur la page commande |
| `actionAdminControllerSetMedia` | Charge le JS back-office sur la page clients |
| `actionEcSmsApprovalCheck` | Hook inter-modules pour interroger le consentement |

## Table créée

| Table | Contenu |
|-------|---------|
| `ecsmsapproval` | Un enregistrement par couple (client, boutique) avec le flag `approval` |

> La table n'est **pas supprimée** à la désinstallation pour préserver les données.

## Utilisation depuis un autre module

```php
$result = Hook::exec('actionEcSmsApprovalCheck', ['id_customer' => $idCustomer]);
// $result = ['has_record' => true/false, 'approved' => true/false/null]
```

Ou en accès direct :

```php
$approved = EcSmsApproval::getCustomerApproval($idCustomer); // bool|null
```

## Installation

1. Copier le dossier `ecsmsapproval` dans `modules/`.
2. Installer depuis le back-office : **Modules > Gestionnaire de modules**.
3. Consulter les consentements via le menu **SMS Approval** dans le back-office.

## Compatibilité

PrestaShop 1.7.1+ — Multiboutique supporté

## Auteur

Ether Creation

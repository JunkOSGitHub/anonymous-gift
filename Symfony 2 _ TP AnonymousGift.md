Symfony 2 : TP AnonymousGift
===================

Sommaire
----- 
* Présentation du contexte
* Analyse UML
	* Flow
	* Entités
		* User
		* Group
		* Gifts
* Installation de SF2
 	* Composer install
	* Installation du vHost
* Création du modèle de donnée
	* Entities
	* Génération de la BDD
* US Stories
* Consigne autre + aide supplémentaire

----------

Contexte
-------------
Nous allons créer un site qui nous permet de créer un évènement, d'inviter des amis et de tous arriver avec 1 cadeau et de repartir avec un autre cadeau. 

Le principe est donc simple, il y a un administrateur qui créé un évènement et qui invite des amis par email. 

Les gens reçoivent le mail avec un lien et s'inscrivent à l'évènement. Une fois que l'on a rassemblé tout le monde, on fait passer un algorithme qui répartie les personnes aléatoirement et qui s'assurent qu'il n'y a bien qu'une seule boucle, pour éviter que 2 personnes s'offre mutuellement un cadeau. 

On ajoutera aussi un petit formulaire pour aider la personne qui recevra notre nom, à choisir un cadeau qui nous fera plaisir. 

> **Remarque:**
> Toute idée bonus sera bonne à prendre. 

Analyse UML
-------

#### Cycle de vie
```flow
st=>start: Start
e=>end
inscription=>operation: Inscription de l'utilisateur
createAccount=>operation: Création du compte utilisateur
createEvent=>operation: Création de l'évènement
invitFriends=>operation: Invitation des amis par email
launchReady=>condition: Tous les invités ont validé ?
algoOk=>operation: Création de la répartition
notifEmail=>operation: Envoi d'un email avec la répartition

st->inscription->createAccount->createEvent->invitFriends->launchReady
launchReady(yes)->algoOk->notifEmail->e
launchReady(no)->invitFriends
```

#### Modèle de donnée
* User
	* lastname (string) 
	* firstname (string)
	* email (string)
* Event
	* startdate (datetime)
	* name (string)
	* owner (user)
	* token (string)
	* is_distributed (boolean)
	* shared_token (string)
* UserEvent
	* user (user)
	* event (event)
	* received_user (user) // Utilisateur a qui on doit offrir un cadeau
	* 

Création du projet
-------

### 1. Installation de SF2
On commence par installer SF2 en utilisant Composer (ou autre)
```shell
$ php composer.phar create-project symfony/framework-standard-edition anonymous-gift "2.8.*"
```
On ferra attention à bien préciser une base de donnée qui s'appelle anonymous-gift et de spécifier un utilisateur mysql qui a les droits suffisants pour se connecter à la BDD. 

### 2. Installation du vHost

```shell
## /etc/apache2/sites-available/anonymous-gift.conf
<VirtualHost *:80>
 ServerName anonymous-gift.local
 DocumentRoot /home/fabien/workspace/anonymous-gift/web
 <Directory /home/fabien/workspace/anonymous-gift/web>
   AllowOverride All
   Require all granted
 </Directory>

</VirtualHost>
```

N'oubliez pas le vhost local
```shell
## /etc/hosts
127.0.0.1 anonymous-gift.local
```
### 3. Création d'un nouveau bundle

```shell
$ php app/console generate:bundle --namespace=Acme/Bundle/BlogBundle
```

### 4. Création des entités

On crée les entités dans notre nouveau bundle dans le dossier Entity en suivant le modèle de donnée donné plus haut. 

```shell
$ php app/console generate:doctrine:entity
```
On créé notre base de donnée et on met à jour le modèle de donnée avec les commandes suivantes : 

```shell
$ php app/console doctrine:generate:database
$ php app/console doctrine:schema:update --force
```

### 5. User stories

> #### US-0 : La page Home
> En tant que visiteur, je veux me rendre sur la HomePage afin de me connecter, créer un compte et afficher de bref information sur notre site. 

On va créer une page HTML très simple, avec un petit descriptif, un lien connexion et un lien nous permettant de nous créer un nouveau compte. 

> #### US-1 : La connexion
> En tant que visiteur je veux créer un compte afin d'accéder à la partie connecté de l'application.

* On commence par installer le [FOSUserBundle](https://github.com/FriendsOfSymfony/FOSUserBundle) afin de gérer la connexion à notre base application.
* On validera que l'on peut utiliser la route `/register` pour créer un compte.
* Attention, il faudra surement mettre à jour la base de donnée, afin de ne pas perdre nos données en permanence, on installera le [DoctrineFixtureBundle]
 (http://symfony.com/doc/current/bundles/DoctrineFixturesBundle/index.html) et on créera une fixture avec un utilisateur (user / user) qui nous servira d'utilisateur de base.
* Après la connexion, on redirige l'utilisateur sur le listing de ses évènements. 
 
> #### US-2 : Listing évènement
> En tant qu'utilisateur, lorsque je me connecte, je suis redirigé sur une page qui liste mes évènements en cours ou futur. 

* On crée une page qui liste simplement les évènements qui ont une date StartDate plus grande que maintenant. 
* Au pied de cette page, on aura un lien "Ajouter un évènement" afin de permettre à l'utilisateur de créer un évènement. 

> #### US-3 : Création d'un évènement. 
> En tant qu'utilisateur, je veux afficher un formulaire afin de créer un nouvel évènement. 

* On créé un nouveau Type qui contient les champs suivants, autour de l'entité Event
	* startDate (datetime) 
	* name (string)
* On crée une nouvelle page, qui affiche le formulaire et qui permet de le valider dans le même controller. 
* On utilisera [DoctrineExtension](https://github.com/stof/StofDoctrineExtensionsBundle) pour s'assurer de générer un token unique `md5(timestamp().rand(0,999999))` lors d'un PrePersist. De même pour le shared_token.  
* On ajoutera un évènement `PRE_SET_DATA` pour s'assurer de bien lier notre nouvel évènement à l'utilisateur connecté. 

> #### US-4 : Page de l'évènement
> En tant qu'utilisateur, je veux pouvoir afficher la page d'un évènement

* On créé une page pour afficher l'évènement qui comprendra : 
	* Une liste des personnes qui participent déjà à l'évènement
	* Un bouton pour ajouter de nouveaux participants
	* Si je suis l'owner de l'évènement, un bouton pour répartir les personnes. 

> #### US-5 : Invitation des amis
> En tant qu'utilisateur, une fois que mon évènement est créé, je veux inviter mes amis à rejoindre la plateforme par email.

* On créé un nouveau Type qui aura les champs suivants :
	* Adresse email
	* Message
* On créé une page qui contiendra :  
	* le message suivant : "Votre évènement a bien été créé. Partagez le lien suivant {url('event_shared_url', {"shared_token" : shared_token}, true)} au près de vos amis pour les inviter à vous rejoindre. 
	* Le nouveau formulaire pour notifier les amis, avec comme message par défault, le texte suivant : "Votre ami {firstname} {lastname}, vous invite à le rejoindre sur anonymous-gift.local en cliquant sur le lien suivant : {path('event_shared_url', {"shared_token" : shared_token}, true)}"
* La validation du formulaire se fait dans le même controller que l'affichage du formulaire. Si le formulaire est valide, on récupère les informations de ce formulaire et [on envoie un mail](http://symfony.com/doc/current/cookbook/email/email.html) à l'adresse email avec le message fourni. On ajoute un flash message pour dire que l'invitation est bien parti, et on laisse l'utilisateur envoyer un nouvel email. 

> #### US-6 : Participation à un évènement
> En tant qu'utilisateur, lorsque j'ai cliqué sur le lien et que je suis connecté, je suis ajouté à la liste des participants de l'évènement. 

* On créé une route /event/{shared_token} qui pointe vers un controller specifique. 
* Dans ce dernier, on récupère l'utilisateur connecté, on récupère l'évènement lié à la route, et on créé un UserEvent qui contient les deux.
* Une fois que l'on a créé le UserEvent, on redirige l'utilisateur sur la page de l'évènement

> #### US-7 : Génération de l'algorithme de répartition
> En tant qu'utilisateur, lorsque je suis le créateur d'un évènement, je peux lancer la génération de la répartition. 

* On créé une nouvelle classe qui implémente un algorithme très simple
	* On prend tous les participants
	* On garde le premier en mémoire
	* On prend un utilisateur au hasard dans les autres
	* On assigne cette personne au premier
	* On recommence jusqu'au dernier qui lui prendra le premier. 
* On créé une nouvelle route, vers un controlleur spécifique qui fait appelle à notre algorithme. 
* On récupère tous les UserEvent qui auront été mis à jour et on envoie un simple mail à tous avec le nom de la personne qui leur est attribué. 

Consigne Bonus
----

* Utilisez un repos Git pour sauvegarder votre travail et revenir en arrière si besoin. 
* Utilisez des Fixtures tout au long de votre développement pour permettre de relancer aussi les infos que vous voulez. 
* N'hésitez pas à utiliser les tests unitaires pour vous assurer que votre application fonctionne bien. Il devrait être écrit en amont du traitement de chaque US. 
* Prenez les US les unes à la suite des autres, mais en ayant quand même conscience de tout ce que vous avez à faire. Si vous voyez que vous avez besoin d'un élément, mais qu'il sera traité dans une autre US, ne mettez en place que le strict minimum pour vous permettre de passer la US.


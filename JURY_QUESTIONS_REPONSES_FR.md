# Banque de questions-réponses jury (Helpdesk System)

## 1) Pitch, vision, produit

### Q1. Pouvez-vous présenter votre projet en 2-3 minutes ?

R. Notre application est une plateforme de support client multi-tenant. Chaque entreprise a son propre sous-domaine, ses opérateurs, ses tickets, ses règles d’automatisation, sa base de connaissance, et ses réglages IA. Le flux principal est: création ticket (widget ou agent), qualification (catégorie/priorité), affectation (automatique ou manuelle), conversation en temps réel client-agent, suivi SLA, notifications temps réel, puis résolution/fermeture avec règles de cycle de vie.

### Q2. Quel problème principal résolvez-vous ?

R. La dispersion du support (emails, messages, oublis, retards) et le manque de traçabilité. Le système centralise les demandes, impose une structure (statut, priorité, SLA), et accélère la réponse grâce à l’automatisation et au temps réel.

### Q3. Qui sont vos utilisateurs ?

R. Trois profils principaux:

- Admin: configure l’entreprise, les opérateurs, les SLA, l’automatisation, l’IA.
- Opérateur: traite les tickets, répond, ajoute des notes internes, collabore en équipe.
- Client final: crée et suit son ticket via widget/portail de suivi.

### Q4. Quel est votre périmètre fonctionnel aujourd’hui ?

R. Ticketing complet, chat temps réel, notifications in-app temps réel, règles d’automatisation, SLA, base de connaissance publique, chatbot widget, auth classique + Google OAuth, gestion équipes/opérateurs, analytics, et tests automatisés.

### Q5. Quel est votre différenciateur ?

R. Le couplage fort entre: temps réel (conversation + notifications), automatisation métier configurable, SLA, et IA assistive (suggestion/résumé/chatbot) dans une architecture Laravel/Livewire cohérente.

## 2) Architecture globale

### Q6. Quelle architecture applicative avez-vous adoptée ?

R. Une architecture web full-stack Laravel avec composants Livewire pour l’UI réactive serveur, Alpine.js pour micro-interactions côté client, Reverb + Echo pour le temps réel, et un modèle de données relationnel centré sur tickets, replies, users, teams, policies et automation rules.

### Q7. Monolithique ou microservices ? Pourquoi ?

R. Monolithe modulaire. Pour ce stade projet, c’est plus rapide à livrer, plus simple à maintenir, et cohérent pour une petite/moyenne équipe. On garde une séparation claire des responsabilités via services, observers, Livewire components, events, notifications et commandes planifiées.

### Q8. Comment gérez-vous le multi-tenant ?

R. Principalement par sous-domaine entreprise + company_id sur les données + scope global Eloquent côté modèles clés. On identifie l’entreprise depuis le host, puis on filtre les requêtes par company_id pour l’isolation logique.

### Q9. Pourquoi ce choix de sous-domaines par entreprise ?

R. Isolation claire de l’espace client, branding naturel, URLs explicites, et meilleure séparation des contextes sans complexité d’un true multi-database par défaut.

### Q10. Où est la logique métier “complexe” ?

R. Dans des services dédiés (ex: moteur d’automatisation, assignation intelligente), observer ticket pour les effets de cycle de vie, et commandes planifiées pour le traitement périodique (SLA, escalades, lifecycle, cleanup).

## 3) Choix technologiques

### Q11. Pourquoi PHP + Laravel 12 ?

R. Laravel donne un framework mature: auth, events, notifications, queues, scheduler, broadcasting, ORM, validation, tests. On gagne en productivité et en qualité sans réinventer l’infrastructure.

### Q12. Pourquoi Livewire 4 au lieu d’un SPA React/Vue complet ?

R. Le domaine est fortement orienté CRUD + workflows métier. Livewire permet une UI dynamique en gardant la logique côté PHP, ce qui réduit la complexité front/back séparée et accélère le delivery.

### Q13. Pourquoi Alpine.js est utilisé ?

R. Pour les micro-interactions UI locales (dropdowns, modals, transitions, dark mode, comportements instantanés) sans surcharge d’un framework frontend lourd. C’est idéal en complément de Livewire.

### Q14. Pourquoi vous avez quand même du JavaScript si vous utilisez Livewire ?

R. Trois raisons:

- Temps réel navigateur: Laravel Echo/Reverb (abonnements channels).
- Bibliothèques UI/graphiques: Chart.js, éditeur rich text, export PDF.
- Interactions fines côté client: Alpine.js + événements CustomEvent.

### Q15. Pourquoi Tailwind CSS 4 ?

R. Productivité élevée, design system utilitaire cohérent, responsive rapide, maintenance facile sans multiplier des fichiers CSS complexes.

### Q16. Pourquoi Reverb + Echo pour le temps réel ?

R. Intégration native Laravel broadcasting, bonne cohérence avec événements/notifications, et architecture robuste pour réponses/typing/notifications en direct.

### Q17. Pourquoi Fortify ?

R. Fortify fournit un backend d’auth sécurisé et extensible (login, reset password, 2FA), ce qui réduit le risque sécurité et standardise les flux d’auth.

### Q18. Pourquoi Socialite ?

R. Pour intégrer Google OAuth proprement avec peu de code custom, tout en gardant le contrôle sur les règles métier (compte invité, vérification, liaison email/google_id).

### Q19. Pourquoi Mews Purifier ?

R. Pour nettoyer le HTML des messages et réduire les risques XSS dans les conversations et contenus riches.

### Q20. Pourquoi Pest pour les tests ?

R. Syntaxe concise, lisible, rapide à écrire/maintenir, et excellente intégration Laravel pour Feature tests (Livewire, auth, services, notifications, etc.).

## 4) Base de données et SGBD

### Q21. Quel SGBD utilisez-vous ?

R. Le projet est configuré par défaut sur SQLite (actuel), avec support prévu aussi pour MySQL/MariaDB/PostgreSQL via configuration Laravel.

### Q22. Pourquoi SQLite dans ce projet ?

R. Simplicité pour démarrage local, setup instantané, migrations/tests rapides. Très pratique en phase de développement et démonstration.

### Q23. Pourquoi ne pas être resté uniquement en fichiers JSON/noSQL ?

R. Le domaine ticketing demande des relations fortes (tickets-users-teams-categories-replies-SLA), intégrité référentielle, requêtes fiables et filtrage riche. Le relationnel est plus adapté.

### Q24. Quelles tables sont les plus importantes ?

R. tickets, ticket_replies, users, customers, ticket_categories, teams/team_user, notifications, sla_policies, automation_rules, company_ai_settings, kb_articles/kb_categories.

### Q25. Pourquoi avoir créé la table tickets ?

R. C’est l’entité métier centrale: numéro, sujet, description, statut, priorité, assignation, SLA, source, tracking, parent/enfant pour follow-up.

### Q26. Pourquoi une table ticket_replies séparée ?

R. Pour modéliser l’historique conversationnel (messages client/agent, notes internes, pièces jointes), indépendamment des métadonnées du ticket.

### Q27. Pourquoi une table customers au lieu de stocker juste nom/email dans tickets ?

R. Normalisation + réutilisation: un client peut avoir plusieurs tickets, on garde un profil consolidé et on évite les duplications.

### Q28. Pourquoi une table automation_rules ?

R. Pour rendre l’automatisation configurable par entreprise sans hardcoder les règles dans le code.

### Q29. Pourquoi une table sla_policies ?

R. Chaque entreprise peut avoir ses propres délais/paramètres SLA et lifecycle (warning, auto-close, reopen window, etc.).

### Q30. Pourquoi des tables notifications + broadcast ?

R. notifications stocke la persistance (audit utilisateur/non-lu). Le broadcast apporte la réactivité instantanée côté UI.

### Q31. Quelles relations clés devez-vous expliquer au jury ?

R.

- Company 1..N Users
- Company 1..N Tickets
- Customer 1..N Tickets
- Ticket 1..N TicketReplies
- User N..N Teams (table pivot team_user)
- Ticket N..1 TicketCategory
- Ticket N..1 Assigned User
- Ticket 1..N child tickets (parent_ticket_id)

### Q32. C’est quoi une clé primaire ?

R. Une colonne qui identifie de façon unique chaque ligne d’une table (ex: id).

### Q33. C’est quoi une clé étrangère ?

R. Une colonne qui référence la clé primaire d’une autre table pour créer une relation (ex: tickets.customer_id -> customers.id).

### Q34. Pourquoi indexer certaines colonnes ?

R. Pour accélérer les filtres fréquents: company_id, status, assigned_to, parent_ticket_id, etc. Indispensable quand le volume augmente.

### Q35. Pouvez-vous expliquer une requête SQL type ?

R. Exemple logique: “Lister les tickets ouverts d’une entreprise assignés à un agent”. On filtre par company_id + assigned_to + status in (open, in_progress, pending), puis tri date ou priorité.

### Q36. Pourquoi CompanyScope global ?

R. Pour éviter les fuites de données cross-tenant par oubli de where(company_id). C’est une garde de sécurité logique systématique.

## 5) Fonctionnalité clé: chat temps réel client-opérateur

### Q37. Comment avez-vous implémenté le chat live ?

R. Avec événements broadcastés via Reverb:

- NewTicketReply pour nouveau message
- TicketTypingUpdated pour indicateur de frappe
  Les composants Livewire client et agent écoutent le channel ticket.{id} via Echo, puis rafraîchissent l’interface instantanément.

### Q38. Pourquoi passer par des événements plutôt que polling ?

R. Moins de latence perçue, moins de requêtes inutiles, meilleure UX en conversation.

### Q39. Quelle donnée est envoyée dans l’événement ?

R. Principalement ticketId. Le composant recharge ensuite les données fraîches côté serveur. C’est plus simple et cohérent que pousser des payloads complets potentiellement incohérents.

### Q40. Comment gérez-vous “is typing” ?

R. Cache avec TTL court (6s) + événement TicketTypingUpdated. Si la clé cache existe, on affiche l’indicateur; expiration naturelle sinon.

### Q41. Pourquoi stocker le nom de l’agent dans le cache typing ?

R. Pour afficher “Agent X est en train d’écrire” côté client au lieu d’un message générique.

### Q42. Que se passe-t-il quand le client répond à un ticket pending ?

R. Le statut repasse en in_progress (la balle revient côté agent).

### Q43. Que se passe-t-il si le ticket est resolved et le client répond ?

R. Si la fenêtre de réouverture n’est pas dépassée, le ticket repasse open.

### Q44. Et si le ticket est fermé (closed) ?

R. Si la fenêtre linked_ticket_days est valide, création d’un ticket follow-up lié au ticket parent.

### Q45. Comment gérez-vous les pièces jointes dans le chat ?

R. Validation stricte (taille/nombre selon contexte), stockage disque public, métadonnées (nom, mime, taille, path) associées à la réponse.

### Q46. Pourquoi nettoyer le contenu des messages ?

R. Sécurité XSS et cohérence de rendu HTML.

## 6) Notifications temps réel

### Q47. Comment fonctionne votre système de notifications ?

R. Laravel Notifications sur canaux database + broadcast. Les événements arrivent en temps réel dans le front via Echo, puis UI met à jour cloche + toasts.

### Q48. Pourquoi conserver la notification en base ?

R. Historique, statut lu/non-lu, persistance même si l’utilisateur n’était pas connecté au moment de l’événement.

### Q49. Comment empêchez-vous un utilisateur d’écouter les notifications d’un autre ?

R. Autorisation de channel privé utilisateur App.Models.User.{id} avec vérification stricte user.id == id channel.

### Q50. Pourquoi avoir des préférences de notifications par utilisateur ?

R. Réduire le bruit et personnaliser l’alerte selon le rôle et les besoins.

## 7) Assignation intelligente et équipes

### Q51. Comment assignez-vous automatiquement un ticket ?

R. Stratégie en cascade:

- spécialiste catégorie
- généraliste disponible
- opérateur disponible compatible
  sinon ticket non assigné + notification admins.

### Q52. Quels critères utilisez-vous ?

R. company_id, rôle opérateur, disponibilité, statut online, charge max (assigned_tickets_count < limite), spécialité catégorie, puis last_assigned_at pour l’équité.

### Q53. Pourquoi suivre assigned_tickets_count ?

R. Pour équilibrer la charge en temps réel et éviter de surcharger un agent.

### Q54. Pourquoi last_assigned_at ?

R. Pour un comportement type round-robin équitable entre agents similaires.

### Q55. Comment gérez-vous l’assignation par équipe ?

R. assignToTeam choisit d’abord un membre spécialisé compatible, puis un généraliste de l’équipe, sinon fallback global.

## 8) SLA, escalade et cycle de vie

### Q56. Comment calculez-vous le due_time SLA ?

R. Depuis la priorité du ticket + policy entreprise, converti selon timezone entreprise, puis stocké en UTC.

### Q57. Quels états SLA utilisez-vous ?

R. on_time, at_risk, breached.

### Q58. Comment détectez-vous les breaches ?

R. Commande planifiée chaque minute qui évalue due_time des tickets non clos/résolus et met à jour sla_status.

### Q59. Que se passe-t-il lors d’un breach ?

R. Notification assigné + exécution des règles automation type sla_breach, sinon fallback notification admin.

### Q60. Quelle différence entre escalade et SLA breach ?

R. SLA breach = dépassement deadline SLA. Escalade = inactivité ticket sur une durée (idle) selon règle.

### Q61. Quels jobs planifiés clés devez-vous connaître ?

R.

- check SLA breaches (chaque minute)
- process escalations (toutes les 15 min)
- process ticket lifecycle (chaque heure)
- cleanup old tickets (quotidien)
- mark inactive users offline (chaque minute)

### Q62. Pourquoi warning + auto-close ?

R. Gouvernance du backlog: éviter tickets résolus dormants, informer client avant fermeture automatique, maintenir file saine.

## 9) Moteur d’automatisation

### Q63. À quoi sert votre moteur d’automatisation ?

R. Exécuter des règles configurables par entreprise sur les tickets sans coder à chaque besoin.

### Q64. Quels types de règles avez-vous ?

R. assignment, keyword_assignment, priority, auto_reply, escalation, sla_breach.

### Q65. Comment s’exécute une règle ?

R. handler type -> evaluate(rule, ticket) -> apply(rule, ticket) -> recordExecution.

### Q66. Pourquoi séparer evaluate et apply ?

R. Meilleure lisibilité, testabilité, extensibilité, et principe single responsibility.

### Q67. Pourquoi exclure certaines règles au create ticket ?

R. escalation et sla_breach sont temporelles, donc traitées par scheduler, pas au moment création.

### Q68. Que mesurez-vous sur les règles ?

R. executions_count + last_executed_at pour audit et observabilité métier.

## 10) Auth, sécurité, permissions

### Q69. Comment gérez-vous l’authentification ?

R. Fortify (login/reset/2FA) + Socialite pour OAuth Google.

### Q70. Comment gérez-vous les utilisateurs invités sans mot de passe ?

R. Cas spécifique: s’ils tentent OAuth Google et password null, redirection vers flow set-password pour finaliser le compte.

### Q71. Comment évitez-vous les doublons de compte avec Google ?

R. Recherche d’abord par email, puis google_id; mise à jour google_id/avatar sur compte existant au lieu de créer un duplicat.

### Q72. Quelle stratégie de contrôle d’accès utilisez-vous ?

R. Middlewares, rôles (admin/operator), gates (ex view-operators), et vérifications contextuelles (ex opérateur hors équipe/assignation bloqué).

### Q73. Quelles protections sécurité clés avez-vous ?

R.

- Validation serveur stricte
- Purification HTML
- Channels privés autorisés
- Rate limiting login/2FA/chatbot
- Vérification email/tokens tracking
- Scope multi-tenant

### Q74. Pourquoi limiter login/2FA ?

R. Réduire brute-force et abus.

### Q75. Comment protégez-vous l’accès client ticket ?

R. URL tracking avec ticket_number + tracking_token + ticket verified.

## 11) Frontend et UX

### Q76. Comment le frontend communique avec le backend ?

R. Livewire actions (requêtes AJAX automatiques), formulaires HTTP classiques, et WebSockets Echo pour événements temps réel.

### Q77. Comment les données sont envoyées au serveur ?

R. Via binding Livewire, formulaires POST validés, et payload JSON pour endpoints chatbot/API.

### Q78. Pourquoi Flux UI/Livewire components ?

R. Cohérence UI, rapidité de composition, et meilleure maintainability que des composants ad hoc dispersés.

### Q79. Pourquoi Chart.js dans ce projet ?

R. Visualiser KPI support (volumes, statuts, priorités, tendances) sur dashboard/reports.

### Q80. Pourquoi un éditeur rich text ?

R. Réponses support et notes plus expressives (liens, formatage), tout en conservant sanitization.

## 12) IA dans le projet

### Q81. Comment avez-vous utilisé l’IA concrètement ?

R.

- Suggestion de réponse agent (copilote)
- Résumé de ticket
- Chatbot widget orienté KB
- Historique conversations agent IA

### Q82. Quels providers/modèles IA supportez-vous ?

R. Configuration multi-provider (Gemini, OpenAI, Anthropic, etc.) via réglages et clés environnement.

### Q83. Pourquoi permettre plusieurs modèles IA ?

R. Arbitrage coût/latence/qualité selon contexte entreprise.

### Q84. Comment gérez-vous les limites/rate limits IA ?

R. Catch exceptions + messages fallback, logique locale de secours dans le chatbot, et seuil d’escalade vers formulaire ticket.

### Q85. Est-ce que vous modifiez le code généré par IA ?

R. Oui, systématiquement. L’IA accélère le draft, mais le code final est validé, corrigé, testé et adapté aux conventions du projet.

### Q86. Comment vérifiez-vous que le code IA est correct ?

R. Relecture architecture/sécurité, tests Pest (feature/unit), validation des cas limites, et cohérence avec règles métier existantes.

### Q87. Quelles limites de l’IA avez-vous rencontrées ?

R. Hallucinations, réponses hors contexte, fragilité sur edge-cases métier. D’où fallback déterministe, prompts stricts et validation humaine.

### Q88. Pourquoi votre chatbot ne répond pas à tout ?

R. Choix produit: prioriser la précision sur domaine support entreprise; hors périmètre -> refus poli/escalade.

### Q89. Comment décidez-vous d’escalader vers humain ?

R. Intention explicite utilisateur ou accumulation de tours non résolus au-delà d’un seuil configurable.

## 13) Qualité, tests, observabilité

### Q90. Comment garantissez-vous la qualité ?

R. Large couverture de tests Feature (auth, chat, automation, SLA, notifications, teams, IA settings, widget, KB, etc.), tests Unit ciblés, plus conventions framework.

### Q91. Quels tests prouvent le chat temps réel/cycle ?

R. Tests dédiés TicketChat couvrent replies client/agent, transitions statut, typing indicator, notifications, emails et follow-up tickets.

### Q92. Avez-vous des tests auth OAuth Google ?

R. Oui, tests dédiés sur redirection invited user set-password et non-duplication comptes existants.

### Q93. Comment validez-vous la non-régression automation/SLA ?

R. Tests services et features (AutomationEngine, règles automation, SLA config/timezone/lifecycle).

### Q94. Avez-vous des logs/audits métier ?

R. Oui, ticket_logs pour actions significatives (assignation, changement statut/priorité, résolution, etc.).

### Q95. Pourquoi c’est important pour le jury ?

R. Parce que ça démontre maintenabilité et capacité à expliquer/justifier les comportements en production.

## 14) Performance et scalabilité

### Q96. Que se passe-t-il si le volume de tickets augmente ?

R. L’architecture peut évoluer en:

- DB plus robuste (MySQL/PostgreSQL)
- cache/queues plus intensifs
- séparation services IA/temps réel si nécessaire
- indexation et optimisation requêtes

### Q97. Quels points peuvent devenir des goulots ?

R. Requêtes non indexées, broadcasting massif, prompts IA coûteux, traitements synchrones lourds.

### Q98. Comment anticipez-vous cela ?

R. Jobs planifiés, canaux ciblés, règles par priorité, limitation payload, fallback local chatbot, et possibilité de déporter certains traitements.

## 15) Questions “pièges” fréquentes

### Q99. Pourquoi ne pas avoir fait une app mobile native ?

R. Le besoin principal est B2B web support/back-office + widget web embarquable; le web répond vite au besoin avec meilleur time-to-market.

### Q100. Pourquoi ne pas avoir choisi un front SPA pur ?

R. Surcoût architecture et coordination front/back pour un gain faible dans ce contexte. Livewire + Alpine apporte déjà une UX réactive.

### Q101. Pourquoi votre système n’est pas uniquement email ?

R. L’email seul n’offre pas assez de structuration: SLA, assignation, vues team, automatisation, suivi statut, analytics, collaboration interne.

### Q102. Si on retire les événements temps réel, impact ?

R. L’application fonctionne encore en mode rafraîchissement manuel, mais UX dégradée: délais visibilité replies/notifications/typing.

### Q103. Si on retire TicketObserver, impact ?

R. Rupture de flux automatiques (due_time SLA initial, automation on create/verify, fallback assignment), incohérences métier.

### Q104. Si on retire CompanyScope, impact ?

R. Risque critique de mélange données entre entreprises.

### Q105. Si on retire la validation serveur, impact ?

R. Risques sécurité, données invalides, incidents métier et techniques.

## 16) Roadmap et amélioration

### Q106. Quelles difficultés avez-vous rencontrées ?

R. Synchronisation temps réel cohérente client/agent, gestion statuts cycle de vie, edge-cases auth Google/invitation, et calibrage fallback IA.

### Q107. Si vous aviez plus de temps, que feriez-vous ?

R.

- Dashboard observabilité plus poussé (latence SLA, qualité réponse)
- plus de tests E2E navigateur
- politiques d’automation avancées (conditions composées)
- analytics IA (précision/fallback/escalation)

### Q108. Comment le projet peut évoluer ?

R. Multi-régions, intégrations omnicanal (mail/chat externe), SLA business hours avancé, routage IA plus fin, API publique plus large, RBAC granulaire.

## 17) Mini-FAQ technique ultra-courte (réponses flash)

### Q109. Pourquoi Laravel ?

R. Productivité + sécurité + écosystème complet.

### Q110. Pourquoi Livewire ?

R. Réactivité UI sans séparer fortement front/back.

### Q111. Pourquoi Alpine.js ?

R. Micro-interactions légères côté client.

### Q112. Pourquoi JS quand même ?

R. WebSockets, charts, éditeur riche, interactions client.

### Q113. Pourquoi SQLite ici ?

R. Simplicité locale, rapidité dev/tests.

### Q114. Pourquoi architecture orientée services/events ?

R. Lisibilité, testabilité, évolutivité.

### Q115. Pourquoi scheduler ?

R. Automatiser tâches temporelles fiables (SLA/escalade/lifecycle).

### Q116. Pourquoi notifications DB + broadcast ?

R. Persistance + instantanéité.

### Q117. Pourquoi IA assistive et non autonome totale ?

R. Garder contrôle humain sur décisions support critiques.

### Q118. Pourquoi autant de tests Feature ?

R. Les bugs majeurs sont dans les flux métier inter-composants.

## 18) Réponses obligatoires demandées (exactement vos questions)

### Pouvez-vous présenter votre projet en quelques minutes ?

Notre projet est une plateforme de helpdesk multi-tenant pour entreprises. Chaque entreprise dispose de son espace dédié (sous-domaine), ses agents, ses tickets, ses règles d’automatisation et ses paramètres SLA. Le client ouvre un ticket via widget/portail, l’agent traite en temps réel, le système notifie, automatise certaines décisions (assignation/priorité/réponses), suit les deadlines SLA et ferme les tickets selon des règles de cycle de vie.

### Quel est le problème que votre application résout ?

Elle résout la désorganisation du support client: demandes perdues, délais non maîtrisés, manque de visibilité équipe, et faible traçabilité.

### Qui sont les utilisateurs de votre application ?

Admins, opérateurs support, et clients finaux.

### Quelles sont les principales fonctionnalités de votre projet ?

Gestion tickets complète, chat temps réel client-agent, notifications temps réel, assignation intelligente, automatisation par règles, SLA + alertes, base de connaissance, chatbot widget, authentification sécurisée (dont Google OAuth), analytics.

### Pourquoi vous avez choisi ces technologies ?

Pour équilibrer vitesse de développement, robustesse, sécurité et maintenabilité: Laravel (socle), Livewire (UI réactive côté serveur), Alpine.js (micro-UX), Reverb/Echo (temps réel), Tailwind (UI rapide), Pest (tests).

### Pourquoi vous avez choisi ce framework ?

Laravel fournit des briques natives fiables (auth, events, notifications, scheduler, ORM, validation) adaptées au domaine helpdesk.

### Pourquoi vous avez choisi ce SGBD ?

SQLite est choisi ici pour simplicité et rapidité en environnement actuel, avec possibilité de basculer vers MySQL/PostgreSQL si besoin de montée en charge.

### Quelles autres technologies vous avez envisagées ?

Front SPA pur (React/Vue), autre backend (Node/Nest), ou autre temps réel. Mais pour le périmètre et l’équipe, Laravel + Livewire était le meilleur compromis vitesse/complexité.

### Pouvez-vous expliquer votre base de données ?

Base relationnelle centrée sur tickets et relations métier: company, users, customers, tickets, replies, categories, teams, notifications, SLA, automation, KB, IA settings. company_id isole les données par tenant.

### Pourquoi vous avez créé ces tables ?

Chaque table correspond à un concept métier isolé (ticket, reply, user, policy, rule, article, etc.) pour normaliser les données, faciliter les requêtes et éviter les duplications.

### Quelles sont les relations entre les tables ?

Exemples: company->users/tickets, customer->tickets, ticket->replies, user<->teams, ticket->category, ticket->assigned user, ticket->child tickets.

### C’est quoi une clé primaire ? une clé étrangère ?

Clé primaire: identifiant unique d’une ligne. Clé étrangère: référence vers la clé primaire d’une autre table pour créer une relation.

### Pouvez-vous expliquer cette requête SQL ?

Oui: on lit d’abord le besoin métier (filtrer par entreprise, statut, assignation), puis on traduit en WHERE/ORDER BY, et on justifie chaque condition par une règle fonctionnelle.

### Pouvez-vous expliquer ce que fait cette fonction ?

Oui: on part de l’entrée, on décrit les validations, la logique métier, les effets en base, puis la sortie (retour, événement, notification, vue).

### Pourquoi vous avez écrit ce code de cette manière ?

Pour séparer responsabilités, faciliter les tests, réduire les effets de bord, et conserver une logique métier explicite.

### Si on enlève cette partie du code, qu’est-ce qui va se passer ?

Tout dépend de la partie, mais on sait expliquer les impacts: perte d’isolation tenant, perte d’automatisation, rupture temps réel, ou baisse sécurité selon le composant retiré.

### Pouvez-vous modifier cette fonction ?

Oui, en respectant les tests existants et en ajoutant des tests pour le nouveau comportement.

### Comment le frontend communique avec le backend ?

Livewire pour les actions UI, HTTP pour certains endpoints, WebSockets via Echo/Reverb pour le temps réel.

### Comment les données sont envoyées au serveur ?

Bindings Livewire, formulaires POST validés, et requêtes JSON pour APIs/chatbot.

### Comment vous gérez les erreurs dans votre application ?

Validation, gestion d’exceptions, notifications utilisateur, fallback logique (notamment IA), logs et tests de non-régression.

### Comment vous avez utilisé l’IA pendant le développement ?

IA utilisée comme assistant: suggestion de structure/implémentation, accélération de drafts, idéation. Le résultat final reste validé et adapté manuellement.

### Est-ce que vous avez modifié le code généré par l’IA ?

Oui, systématiquement pour conformité métier, sécurité, style projet et performance.

### Comment vous vérifiez que le code généré par l’IA est correct ?

Relecture critique, tests automatisés (Pest), tests manuels ciblés, et validation avec les flux métier.

### Quelles sont les limites de l’IA dans votre projet ?

Elle peut se tromper sur le contexte métier et les cas limites. Elle ne remplace pas la validation humaine ni les tests.

### Quelles difficultés vous avez rencontrées ?

Synchronisation temps réel, edge-cases de cycle de vie ticket, auth Google/invitation, et fiabilité des comportements IA/fallback.

### Si vous avez plus de temps, qu’est-ce que vous allez améliorer ?

Observabilité, E2E tests, règles automation plus avancées, meilleure analytique IA, et optimisation montée en charge.

### Comment votre application peut évoluer dans le futur ?

Scalabilité DB/infra, intégrations canaux supplémentaires, API plus riche, automation intelligente avancée, gouvernance sécurité renforcée.

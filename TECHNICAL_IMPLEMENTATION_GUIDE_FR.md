# Guide Technique Complet (niveau Software Engineer)

## 0. Scope et objectifs

Ce document est ecrit pour un public SE. Il decrit:

- l'architecture runtime,
- les flux techniques (write path, read path, async path),
- les invariants metier,
- les garanties de securite/consistance,
- les risques operationnels et les mitigations.

Le but est de pouvoir expliquer "comment c'est construit" et "pourquoi c'est construit comme ca" devant un jury technique.

## 1. Architecture globale

### 1.1 Style architectural

- Monolithe modulaire Laravel 12.
- Couches explicites:
    - HTTP layer: routes, middleware, controllers.
    - Domain layer: services, observers, events, notifications.
    - UI layer: Livewire components + Blade + Alpine.
    - Integration layer: Reverb/Echo, Mail, Laravel AI, scheduler.

### 1.2 Contextes fonctionnels principaux

- Ticketing Core: creation, assignation, reponses, statuts.
- Realtime Collaboration: conversation instantanee + typing.
- Automation Engine: regles conditionnelles executees sur tickets.
- SLA & Lifecycle: due_time, breach, auto-close, cleanup.
- Identity & Security: auth, roles, multi-tenant isolation.
- Knowledge & AI: KB, suggestions, summaries, chatbot.

### 1.3 Sources de verite

- Etat metier durable: base relationnelle.
- Etat transitoire faible latence: cache + broadcast events.
- Etat d'interface: Livewire server state + Alpine local state.

## 2. Multi-tenant: isolation et securite des donnees

### 2.1 Tenant resolution path

1. Le host entrant est inspecte.
2. Le sous-domaine est mappe vers une company.
3. La company est injectee dans le contexte request/view.

### 2.2 Tenant isolation path

1. Les modeles metier portent company_id.
2. Un global scope Eloquent filtre automatiquement par company_id utilisateur.
3. Les routes internes sensibles demandent auth + company.access.

### 2.3 Pourquoi c'est robuste

- Reduit drastiquement les oublis de filtre manuel.
- Les checks metier (role, equipe, assignee) ferment les trous restants au niveau composant.

## 3. Ticket domain model et invariants

### 3.1 Entites critiques

- tickets: aggregate principal (status, priority, assigned_to, SLA fields).
- ticket_replies: timeline (public/internal, attachments).
- customers: profil externe normalise.
- users: acteurs internes (admin/operator).
- ticket_categories + teams: routage et collaboration.
- automation_rules + sla_policies: gouvernance dynamique.

### 3.2 Invariants metier

- Un ticket non verifie ne doit pas entrer dans les flux automatiques critiques.
- La machine a etats ticket reste bornee: open, in_progress, pending, resolved, closed.
- Les fenetres SLA governent reopen/follow-up apres resolution/fermeture.

### 3.3 Lifecycle hooks (Observer)

- creating: initialise due_time.
- updating: recalc due_time sur changement de priorite.
- created: execute automation si verified, puis fallback assignment.
- updated (verified passe a true): meme pipeline automation + fallback.
- updated/deleted/restored: maintenance des compteurs de charge agent.

## 4. Realtime conversation (client <-> operateur)

### 4.1 Stack

- Reverb = WebSocket broker cote serveur.
- Echo = subscription client.
- Events metier: NewTicketReply, TicketTypingUpdated.
- Livewire listeners pour rehydrater les composants.

### 4.2 Message write path

1. Input validation (message, attachments, limites).
2. Sanitization HTML cote serveur (Purifier).
3. Persist dans ticket_replies + metadata attachments.
4. Side effects metier:
    - transitions de statut selon contexte,
    - notifications + emails conditionnels.
5. Broadcast toOthers(NewTicketReply).

### 4.3 Message read path

1. Echo recoit l'event sur ticket.{id}.
2. Livewire listener declenche refresh.
3. Le composant relit la DB et rerender.

Design rationale:

- Event payload minimal,
- coherence SSR Livewire,
- evite la divergence entre etat client et etat base.

### 4.4 Typing protocol

- Cache keys:
    - ticket:typing:customer:{ticketId}
    - ticket:typing:agent:{ticketId}
- TTL court + event de refresh.
- Eventual consistency acceptable car c'est un hint UX, pas une donnee metier.

## 5. Notification pipeline (durable + realtime)

### 5.1 Dual channel architecture

- Database channel: persistance, read/unread, audit.
- Broadcast channel: instant feedback UI.

### 5.2 Delivery flow

1. Une action metier construit une Notification.
2. Le channel set est resolu (preferences, role, contexte).
3. Ecriture dans notifications.
4. Broadcast vers channel prive user.
5. Front:
    - toast transient,
    - refresh NotificationBell / page notifications.

### 5.3 Channel security

- App.Models.User.{id} autorise uniquement pour le proprietaire.

## 6. Automation engine internals

### 6.1 Abstractions

- AutomationEngine = orchestrateur.
- RuleInterface = evaluate(rule, ticket) + apply(rule, ticket).
- Registry type -> handler class.

### 6.2 Execution semantics

- Regles actives chargees ordered by priority.
- Multi-match execution (pas de stop global apres premier match).
- Regles temporelles (escalation, sla_breach) executees par scheduler, pas sur create path.

### 6.3 Observability

- executions_count et last_executed_at.
- logs info/error par execution.

### 6.4 Failure behavior

- Echec d'une regle isole, le moteur continue.
- Si aucun owner apres automation: fallback assignment service.

## 7. Assignment service (smart routing)

### 7.1 Routing strategy

1. Specialist match (category ou parent).
2. Generalist disponible.
3. Any compatible operator under tenant load cap.
4. Sinon unassigned + notification admin.

### 7.2 Constraints appliquees

- same company,
- role operator,
- available et online,
- assigned_tickets_count sous seuil configuré tenant.

### 7.3 Load balancing

- tri par workload ouvert.
- tie-break par last_assigned_at asc.

### 7.4 Team-aware behavior

- assignToTeam favorise les membres specialises.
- fallback global si equipe indisponible.

### 7.5 Consistency guarantees

- ecritures critiques en transaction DB.
- counters mis a jour sur assign/reassign/status transitions.

## 8. SLA subsystem et lifecycle ops

### 8.1 Data model

- sla_policies par company.
- ticket fields: due_time, sla_status, resolved_at, closed_at, warning_sent_at.

### 8.2 due_time path

1. Lecture policy entreprise.
2. Mapping priority -> minutes.
3. Calcul selon timezone entreprise.
4. Normalisation UTC en base.

### 8.3 SLA status machine

- on_time -> at_risk -> breached.
- reset breached possible si priorite change et nouveau due_time futur.

### 8.4 Scheduled jobs

- check-sla-breaches: every minute.
- process-escalations: every 15 minutes.
- process-ticket-lifecycle: hourly.
- cleanup-old-tickets: daily.

### 8.5 Operational safety

- withoutOverlapping sur jobs critiques.
- runInBackground pour ne pas bloquer le scheduler cycle.

## 9. Auth, identity, access

### 9.1 Fortify path

- login, reset password, 2FA.
- auth callback custom pour cas pending invited user.
- rate limit login et two-factor.

### 9.2 Socialite path (Google)

1. Callback OAuth.
2. Resolution user par email, sinon google_id.
3. Evite la creation de doublons.
4. Si password null (invitation incomplete): redirect set-password.

### 9.3 Authorization layers

- Route middleware: auth, verified, company.access.
- Gates role-based (admin/operator).
- Checks contextuels dans composants ticket (assignee/teammate/outsider).

## 10. Rich text (Tiptap) + sanitization model

### 10.1 Pourquoi Tiptap

- Editor extensible.
- Bon DX pour integrer actions contextuelles.
- Bon UX pour reponses support.

### 10.2 Security model

- Rich content possible cote client.
- Persisted content toujours nettoye cote serveur avant stockage/rendu.

### 10.3 Tradeoff

- UX riche sans basculer vers un SPA editor stack complet.

## 11. AI subsystem (assistive)

### 11.1 Features

- Suggestion de reponse agent.
- Resume de ticket.
- Chatbot support.

### 11.2 Provider abstraction

- provider/model resolus depuis les settings entreprise.
- Call sites metier decouples de l'implementation provider.

### 11.3 Reliability patterns

- exception handling explicite (rate limit, provider errors).
- fallback deterministic path dans chatbot.
- escalation vers ticket form apres unresolved turns threshold.

### 11.4 Governance

- IA assistive uniquement.
- pas d'action destructive autonome.
- validation humaine finale.

## 12. Frontend runtime model

### 12.1 Livewire responsibilities

- Server-driven state transitions.
- Validation + business actions.
- Re-render SSR-friendly apres events.

### 12.2 Alpine responsibilities

- Etat local ephemere UI (open/close, transitions, dropdowns).
- glue lightweight autour composants Livewire.

### 12.3 Plain JS responsibilities

- Echo subscriptions et event bridging.
- Chart rendering (analytics).
- PDF/export workflows.
- hooks pour synchronisation post-morph Livewire.

## 13. Features supplementaires souvent oubliees

### 13.1 Knowledge Base (KB)

- Public portal par entreprise.
- Categories + articles + recherche + vote helpful.
- API KB publique (endpoints lecture/search).

Flow KB publication:

1. Admin cree/edite article.
2. Versioning article stocke.
3. Status publie controle la visibilite publique.

### 13.2 Widget ticket + verification email

- Widget public embarquable.
- Option require_client_verification.

Flow verification:

1. Ticket cree verified=false + verification_token.
2. Email de verification envoye.
3. Sur clic: verified=true + tracking_token.
4. Observer updated lance automation + assignment fallback.

### 13.3 Customer tracking portal

- Route securisee ticket_number + tracking_token.
- Conversation client visible sans login interne.

### 13.4 Teams et mentions

- team_user pivot pour appartenance equipe.
- Internal notes avec mentions utilisateurs.
- Notification UserMentioned + tracking des mentions lues/non lues.

### 13.5 Presence operateurs

- online/offline/last_activity tracked.
- commande planifiee pour marquer inactifs offline.

### 13.6 Reports & analytics

- Dashboard metrics + charts (Chart.js).
- Exports (CSV/PDF) pour analyse operationnelle.

### 13.7 Onboarding tenant

- wizard initial entreprise.
- provisioning settings (SLA, channels, etc.) avant usage complet.

### 13.8 Notification preferences

- Preferences utilisateur en JSON.
- resolution channel basee sur type de notification.

## 14. Database engineering notes

### 14.1 Schema rationale

- relation-first design pour consistency/reporting.
- foreign keys/indexes sur hot paths (company_id, status, assigned_to, time).

### 14.2 Query behavior

- company_id predicates first-class.
- filtres status/assignee/category/time optimises par index.

### 14.3 Evolution strategy

- migrations incremental/additive.
- colonnes lifecycle et SLA pour retention policy explicite.

## 15. Quality strategy

### 15.1 Framework

- Pest + Laravel testing stack.

### 15.2 Coverage posture

- Feature-heavy pour cross-layer flows.
- Unit pour logique algorithmique (ex fallback/similarity).

### 15.3 Critical paths couverts

- chat lifecycle transitions,
- notification sync,
- automation + SLA,
- auth/oauth edge cases,
- tenant safety,
- team visibility,
- chatbot fallback behavior.

## 16. Failure modes et mitigations

### 16.1 Failure modes

- websocket outage,
- AI provider throttling,
- scheduler overlap,
- assignment counter drift,
- tenant boundary regression.

### 16.2 Mitigations

- DB reste source de verite.
- fallback logic (AI, assignment).
- overlap guards sur scheduler.
- transactions sur ecritures critiques.
- middleware + global scope pour isolation tenant.

## 17. Scalability plan

### 17.1 Current posture

- mono app node possible.
- sqlite en environnement actuel.
- event-driven realtime.

### 17.2 Scale path

- migration DB vers MySQL/PostgreSQL.
- externalisation cache/queue.
- separation workers realtime/web.
- query profiling + index tuning.
- metrics/tracing sur scheduler et broadcasting.

## 18. One-minute engineering pitch

Le systeme est un monolithe modulaire Laravel 12 multi-tenant, avec isolation company_id et resolution tenant par sous-domaine. L'aggregate central est le ticket, enrichi par replies, SLA policies et automation rules. Le realtime utilise Reverb/Echo avec events minimaux (NewTicketReply, TicketTypingUpdated) et rehydratation Livewire cote serveur. L'assignation est geree par un service algorithmique contraint par disponibilite/charge/equite, avec transactions pour consistance. Le moteur d'automation applique des handlers evaluate/apply observables et prioritises. La securite combine Fortify, Socialite, rate limiting, sanitization HTML, channel auth stricte et controles contextuels. La couche IA reste assistive, provider-agnostic, avec fallback deterministic et escalation controlee.

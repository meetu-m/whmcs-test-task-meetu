## Task scope
Create WHMCS addon module that sends hosting addon updates to client sites and sends Slack notifications

- It must use job queue, don't execute any requests in runtime
- It should allow configuration (on addon module page / addon admin page) for which hosting addons it should be active

### Task details
1. When hosting addon is added / changed / cancelled it should add a job to the queue (see the full hooks list in the code, also see [WHMCS Hooks Reference](https://developers.whmcs.com/hooks-reference/addon/)) 
2. Job queue is then processed via cron, in batches respecting the time limit
3. Each update should be sent to the client site using cURL post request to a REST endpoint (no need to develop it, just use a dummy URL), data to include - client ID, product ID, service ID, addon ID, hosting addon ID, old and new status for the hosting addon. Preferably, use corresponding server IP (from tblservers) and Host header for these requests, to avoid DNS and CDN overhead.
4. When addon is deleted, it should also send notification to Slack using [Slack API](https://api.slack.com/reference/messaging/attachments)
5. Slack notifications must include client data - name & email, service / hosting details - date started, domain, lifetime value (summary of all paid invoices for this service) 

### Advanced level (*optional*)
1. Skip the job queue if update was initialized from the API
2. Add a widget with statistics for tasks queue
3. Add a button to rerun a failed task
4. Add a submenu item for the addon in the primary sidebar menu using WHMCS\View\Menu\Item

### Theory (*no code required, just explanation*)
1. How you'd change/optimize the architecture of this plugin if the rate of updates for hosting addons is 100/sec? 1000/sec?

## Workflow
1.  Use WHMCS v8.0+ (let us know if you need a dev license / access to dev server)
2.  Clone this repository
3.  Run `composer install` in the plugin folder
4.  Push the result to a public repository on bitbucket/github and send us a link.

## Coding guidelines
1.  PHP (7.4+), preferably indented & formatted uniformly (using PSR / WHMCS / Wordpress coding styles) 
2.  [Laravel query builder](https://laravel.com/docs/10.x/queries)
3.  It must follow patterns from the wireframe plugin - specification for autoloading, namespaces, strict types (if specified), each method must specify arguments and return types (except void), properly use and handle exceptions etc
4.  Add [PHPDoc](https://docs.phpdoc.org/references/phpdoc/index.html) comments for classes/methods, you can comment your code but it's not required.

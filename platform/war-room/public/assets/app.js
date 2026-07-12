(function () {
    'use strict';

    var endpoints = {
        status: '/api/v1/status.php',
        services: '/api/v1/services.php',
        resources: '/api/v1/resources.php',
        containers: '/api/v1/containers.php',
        tasks: '/api/v1/tasks.php',
        manuals: '/api/v1/manuals.php',
        operations: '/api/v1/operations.php'
    };

    var timers = {};
    var inFlight = {
        status: false,
        services: false,
        resources: false,
        containers: false,
        tasks: false,
        manuals: false,
        operations: false
    };

    var failures = {
        status: 0,
        services: 0,
        resources: 0,
        containers: 0,
        tasks: 0,
        manuals: 0,
        operations: 0
    };

    var lastStatusUpdateIso = null;
    var manualsCatalog = [];
    var selectedManualSlug = null;

    function select(selector) {
        return document.querySelector(selector);
    }

    function selectAll(selector) {
        return Array.prototype.slice.call(document.querySelectorAll(selector));
    }

    function setText(selector, value) {
        var node = select(selector);
        if (node) {
            node.textContent = value;
        }
    }

    function formatState(state) {
        if (state === 'operational' || state === 'up') {
            return 'ONLINE';
        }
        if (state === 'degraded') {
            return 'DEGRADADO';
        }
        if (state === 'down') {
            return 'CAÍDO';
        }
        if (state === 'local') {
            return 'LOCAL';
        }
        return 'PENDIENTE';
    }

    function timeAgo(isoTime) {
        var ts = Date.parse(isoTime);
        if (Number.isNaN(ts)) {
            return 'Sin datos';
        }

        var diff = Math.max(0, Math.floor((Date.now() - ts) / 1000));
        return 'hace ' + diff + 's';
    }

    function buildBadgeClass(state) {
        if (state === 'up' || state === 'operational') {
            return 'badge-up';
        }
        if (state === 'local') {
            return 'badge-local';
        }
        if (state === 'degraded') {
            return 'badge-pending';
        }
        return 'badge-risk';
    }

    function fetchJson(url, timeoutMs) {
        var controller = new AbortController();
        var timeout = setTimeout(function () {
            controller.abort();
        }, timeoutMs);

        return fetch(url, {
            method: 'GET',
            headers: { 'Accept': 'application/json' },
            signal: controller.signal,
            cache: 'no-store'
        }).then(function (response) {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            return response.json();
        }).finally(function () {
            clearTimeout(timeout);
        });
    }

    function buildManualUrl(slug) {
        if (!slug) {
            return endpoints.manuals;
        }
        return endpoints.manuals + '?slug=' + encodeURIComponent(slug);
    }

    function scheduleNext(name, baseIntervalMs) {
        var failCount = failures[name];
        var nextDelay = baseIntervalMs;

        if (failCount > 0) {
            nextDelay = Math.min(baseIntervalMs * Math.pow(2, failCount), 20000);
        }

        clearTimeout(timers[name]);
        timers[name] = setTimeout(function () {
            if (name === 'status') {
                pollStatus();
            } else if (name === 'services') {
                pollServices();
            } else if (name === 'resources') {
                pollResources();
            } else if (name === 'containers') {
                pollContainers();
            } else if (name === 'tasks') {
                pollTasks();
            } else if (name === 'manuals') {
                pollManuals();
            } else if (name === 'operations') {
                pollOperations();
            }
        }, nextDelay);
    }

    function taskStatusLabel(status) {
        if (status === 'done') {
            return 'HECHO';
        }
        if (status === 'doing') {
            return 'EN CURSO';
        }
        if (status === 'blocked') {
            return 'BLOQUEADO';
        }
        if (status === 'later') {
            return 'MÁS ADELANTE';
        }
        return 'PENDIENTE';
    }

    function taskBadgeClass(status) {
        if (status === 'done') {
            return 'badge-done';
        }
        if (status === 'doing') {
            return 'badge-local';
        }
        if (status === 'blocked') {
            return 'badge-risk';
        }
        if (status === 'later') {
            return 'badge-later';
        }
        return 'badge-pending';
    }

    function showTaskError(show) {
        var node = select('[data-task-error]');
        if (node) {
            node.hidden = !show;
        }
    }

    function showManualsError(show) {
        var node = select('[data-manuals-error]');
        if (node) {
            node.hidden = !show;
        }
    }

    function renderTasks(payload) {
        var list = select('[data-pending-list]');
        if (!list || !payload || !Array.isArray(payload.blocks)) {
            throw new Error('Invalid tasks payload');
        }

        list.textContent = '';

        payload.blocks.forEach(function (block) {
            var blockLi = document.createElement('li');
            var title = document.createElement('strong');
            var items = Array.isArray(block.items) ? block.items : [];

            blockLi.className = 'task-block';
            title.className = 'task-block-title';
            title.textContent = block.title || 'Checklist';
            blockLi.appendChild(title);
            list.appendChild(blockLi);

            items.forEach(function (item) {
                var li = document.createElement('li');
                var label = document.createElement('span');
                var badge = document.createElement('span');
                var status = item.status || 'pending';

                li.className = 'task-item task-status-' + status;
                label.textContent = item.title || item.id || 'Tarea';
                badge.className = 'badge ' + taskBadgeClass(status);
                badge.textContent = taskStatusLabel(status);

                li.appendChild(label);
                li.appendChild(badge);
                list.appendChild(li);
            });
        });
    }

    function renderManuals(payload) {
        var list = select('[data-manuals-list]');
        if (!list || !payload || !Array.isArray(payload.items)) {
            throw new Error('Invalid manuals payload');
        }

        manualsCatalog = payload.items;
        list.textContent = '';

        payload.items.forEach(function (manual) {
            var li = document.createElement('li');
            var wrapper = document.createElement('div');
            var title = document.createElement('strong');
            var summary = document.createElement('span');
            var badge = document.createElement('span');

            title.textContent = manual.title || manual.id || 'Manual';
            summary.textContent = manual.summary || 'Documento saneado de consulta.';
            badge.className = 'badge ' + (manual.available ? 'badge-local' : 'badge-pending');
            badge.textContent = manual.available ? 'DISPONIBLE' : 'PENDIENTE';

            wrapper.appendChild(title);
            wrapper.appendChild(summary);
            li.appendChild(wrapper);
            li.appendChild(badge);
            list.appendChild(li);
        });

        renderManualCatalog(payload);

        var stateLabel = 'Read-only';
        if (payload.state === 'available') {
            stateLabel = 'Disponible';
        } else if (payload.state === 'partial') {
            stateLabel = 'Parcial';
        } else if (payload.state === 'unavailable') {
            stateLabel = 'Pendiente';
        }

        setText('[data-manuals-state]', stateLabel);

        if (payload.manuals_available === true) {
            setText('[data-manuals-note]', 'Catálogo documental saneado disponible en modo solo lectura.');
        } else if (payload.reason === 'manuals_not_mounted') {
            setText('[data-manuals-note]', 'Catálogo preparado. Montaje de manuales pendiente en el contenedor.');
        } else {
            setText('[data-manuals-note]', 'Catálogo preparado. Algunos manuales aún no están disponibles.');
        }
    }

    function operationStateLabel(state) {
        var labels = {
            applied: 'Aplicado',
            available: 'Disponible',
            closed: 'Cerrado',
            disabled: 'Desactivado',
            local_no_versioned: 'Local / no versionado',
            mounted_read_only: 'Montado read-only',
            not_mounted: 'No montado',
            outside_git: 'Fuera de Git',
            pending: 'Pendiente',
            pending_design: 'Diseño pendiente',
            pending_sanitization: 'Saneamiento pendiente',
            read_only: 'Read-only',
            updated: 'Actualizado'
        };

        return labels[state] || state || 'Pendiente';
    }

    function operationBadgeClass(state) {
        if (state === 'closed' || state === 'applied' || state === 'updated' || state === 'available' || state === 'mounted_read_only' || state === 'read_only' || state === 'disabled' || state === 'not_mounted' || state === 'outside_git') {
            return 'badge-local';
        }

        if (state === 'pending' || state === 'pending_design' || state === 'pending_sanitization') {
            return 'badge-pending';
        }

        return 'badge-up';
    }

    function renderOperations(payload) {
        var grid = select('[data-operations-grid]');
        if (!grid || !payload || !Array.isArray(payload.sections)) {
            throw new Error('Invalid operations payload');
        }

        grid.textContent = '';

        payload.sections.forEach(function (section) {
            var article = document.createElement('article');
            var title = document.createElement('h3');
            var summary = document.createElement('p');
            var list = document.createElement('ul');

            article.className = 'operations-card';
            title.textContent = section.title || 'Diagnóstico';
            summary.textContent = section.summary || 'Información operativa read-only.';

            article.appendChild(title);
            article.appendChild(summary);

            (Array.isArray(section.items) ? section.items : []).forEach(function (item) {
                var li = document.createElement('li');
                var label = document.createElement('span');
                var badge = document.createElement('span');

                label.textContent = item.label || 'Elemento';
                badge.className = 'badge ' + operationBadgeClass(item.state);
                badge.textContent = operationStateLabel(item.state);

                li.appendChild(label);
                li.appendChild(badge);
                list.appendChild(li);
            });

            article.appendChild(list);
            grid.appendChild(article);
        });
    }

    function renderManualCatalog(payload) {
        var list = select('[data-manual-reader-list]');
        if (!list || !payload || !Array.isArray(payload.items)) {
            return;
        }

        list.textContent = '';

        payload.items.forEach(function (manual) {
            var li = document.createElement('li');
            var button = document.createElement('button');
            var title = document.createElement('strong');
            var summary = document.createElement('span');
            var state = document.createElement('em');

            button.type = 'button';
            button.dataset.manualSlug = manual.id;
            button.className = manual.id === selectedManualSlug ? 'is-active' : '';

            title.textContent = manual.title || manual.id || 'Manual';
            summary.textContent = manual.summary || 'Documento saneado de consulta.';
            state.textContent = manual.available ? 'Disponible' : 'Pendiente de mount';

            button.appendChild(title);
            button.appendChild(summary);
            button.appendChild(state);
            li.appendChild(button);
            list.appendChild(li);
        });

        updateManualReaderStatus(payload);
    }

    function updateManualReaderStatus(payload) {
        if (!payload) {
            return;
        }

        if (payload.manuals_available === true) {
            setText('[data-manual-reader-status]', 'Manuales disponibles para lectura online.');
        } else if (payload.reason === 'manuals_not_mounted') {
            setText('[data-manual-reader-status]', 'El catálogo está definido, pero el mount de manuales aún no está aplicado.');
        } else {
            setText('[data-manual-reader-status]', 'Catálogo preparado con disponibilidad parcial.');
        }
    }

    function clearManualContent(title, summary, state) {
        var content = select('[data-manual-content]');
        setText('[data-manual-content-title]', title);
        setText('[data-manual-content-summary]', summary);
        setText('[data-manual-content-state]', state);

        if (content) {
            content.textContent = '';
            var p = document.createElement('p');
            p.textContent = summary;
            content.appendChild(p);
        }
    }

    function appendParagraph(container, lines) {
        var text = lines.join(' ').trim();
        if (!text) {
            return;
        }

        var p = document.createElement('p');
        p.textContent = text;
        container.appendChild(p);
    }

    function renderMarkdownText(markdown) {
        var container = select('[data-manual-content]');
        if (!container) {
            return;
        }

        container.textContent = '';

        var lines = String(markdown || '').replace(/\r\n/g, '\n').split('\n');
        var paragraph = [];
        var list = null;
        var code = null;

        function flushParagraph() {
            appendParagraph(container, paragraph);
            paragraph = [];
        }

        function flushList() {
            if (list) {
                container.appendChild(list);
                list = null;
            }
        }

        function flushCode() {
            if (code) {
                container.appendChild(code);
                code = null;
            }
        }

        lines.forEach(function (line) {
            var trimmed = line.trim();

            if (trimmed.indexOf('```') === 0) {
                flushParagraph();
                flushList();

                if (code) {
                    flushCode();
                } else {
                    code = document.createElement('pre');
                    code.appendChild(document.createElement('code'));
                }
                return;
            }

            if (code) {
                code.firstChild.textContent += line + '\n';
                return;
            }

            if (trimmed === '') {
                flushParagraph();
                flushList();
                return;
            }

            if (trimmed.indexOf('# ') === 0 || trimmed.indexOf('## ') === 0 || trimmed.indexOf('### ') === 0) {
                var level = trimmed.indexOf('### ') === 0 ? 4 : (trimmed.indexOf('## ') === 0 ? 3 : 2);
                var heading = document.createElement('h' + level);

                flushParagraph();
                flushList();
                heading.textContent = trimmed.replace(/^#{1,3}\s+/, '');
                container.appendChild(heading);
                return;
            }

            if (trimmed.indexOf('- ') === 0) {
                var item = document.createElement('li');

                flushParagraph();
                if (!list) {
                    list = document.createElement('ul');
                }
                item.textContent = trimmed.slice(2);
                list.appendChild(item);
                return;
            }

            paragraph.push(trimmed);
        });

        flushParagraph();
        flushList();
        flushCode();
    }

    function loadManual(slug) {
        if (!slug) {
            clearManualContent('Selecciona un manual', 'El contenido se cargará desde la API read-only cuando esté disponible.', 'Sin selección');
            return;
        }

        selectedManualSlug = slug;
        renderManualCatalog({ items: manualsCatalog });
        clearManualContent('Cargando manual', 'Solicitando contenido por API read-only.', 'Cargando');

        fetchJson(buildManualUrl(slug), 2500).then(function (data) {
            var manual = data && data.data ? data.data.manual : null;
            if (!data || data.ok !== true || !manual) {
                throw new Error('Manual API failed');
            }

            setText('[data-manual-content-title]', manual.title || slug);
            setText('[data-manual-content-summary]', manual.summary || 'Manual saneado de consulta.');

            if (manual.available && typeof manual.content === 'string') {
                setText('[data-manual-content-state]', 'Read-only');
                renderMarkdownText(manual.content);
            } else {
                setText('[data-manual-content-state]', 'Pendiente');
                clearManualContent(
                    manual.title || slug,
                    'El manual existe en el catálogo, pero el contenido aún no está montado en el contenedor.',
                    'Pendiente'
                );
            }
        }).catch(function () {
            clearManualContent('Manual no disponible', 'No se pudo cargar el contenido de forma segura.', 'Sin datos');
        });
    }

    function routeTo(hash) {
        var target = hash || window.location.hash || '#dashboard';
        var isManuals = target.indexOf('#manuales') === 0;
        var isOperations = target.indexOf('#operaciones') === 0;
        var manualView = select('[data-manual-reader-view]');
        var operationsView = select('[data-operations-view]');

        document.body.classList.toggle('is-manuals-route', isManuals);
        document.body.classList.toggle('is-operations-route', isOperations);
        if (manualView) {
            manualView.hidden = !isManuals;
        }
        if (operationsView) {
            operationsView.hidden = !isOperations;
        }

        selectAll('[data-nav-item]').forEach(function (link) {
            var href = link.getAttribute('href') || '';
            var activeHref = '#dashboard';
            if (isManuals) {
                activeHref = '#manuales';
            } else if (isOperations) {
                activeHref = '#operaciones';
            }
            link.classList.toggle('is-active', href === activeHref);
        });

        if (isManuals) {
            var parts = target.split('/');
            var slug = parts.length > 1 ? decodeURIComponent(parts[1]) : null;
            if (slug) {
                loadManual(slug);
            }
        }
    }

    function renderClock() {
        var now = new Date();
        setText('[data-clock-time]', now.toLocaleTimeString('es-ES', { hour12: false }));
        setText('[data-clock-date]', now.toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' }));

        if (lastStatusUpdateIso) {
            setText('[data-status-updated]', timeAgo(lastStatusUpdateIso));
        }
    }

    function pollStatus() {
        if (inFlight.status) {
            scheduleNext('status', 5000);
            return;
        }

        inFlight.status = true;

        fetchJson(endpoints.status, 2500).then(function (data) {
            failures.status = 0;

            setText('[data-status-mode]', 'Modo ' + (data.mode || 'dynamic'));
            setText('[data-status-ring]', formatState(data.state));
            setText('[data-status-ring-label]', 'DATOS REALES');
            setText('[data-status-services]', data.telemetry && data.telemetry.services === 'real' ? 'Conectados' : 'Pendiente');
            setText('[data-status-hosts]', '1 local');
            if (data.last_update) {
                lastStatusUpdateIso = data.last_update;
                setText('[data-status-updated]', timeAgo(lastStatusUpdateIso));
            }
            setText('[data-overview-state]', formatState(data.state));
            setText('[data-overview-telemetry]', 'Servicios en tiempo real / Recursos pendientes');
        }).catch(function () {
            failures.status += 1;
            if (failures.status >= 3) {
                setText('[data-status-mode]', 'Datos no disponibles');
                setText('[data-status-ring]', 'STALE');
                setText('[data-status-ring-label]', 'REINTENTANDO');
            }
        }).finally(function () {
            inFlight.status = false;
            scheduleNext('status', 5000);
        });
    }

    function pollServices() {
        if (inFlight.services) {
            scheduleNext('services', 5000);
            return;
        }

        inFlight.services = true;

        fetchJson(endpoints.services, 2500).then(function (data) {
            failures.services = 0;

            var list = select('[data-service-list]');
            if (!list || !Array.isArray(data.items)) {
                return;
            }

            list.textContent = '';

            data.items.forEach(function (item) {
                var stateLabel = formatState(item.state);
                var badgeClass = buildBadgeClass(item.state);
                var note = item.http_code ? ('HTTP ' + item.http_code + ' · ' + (item.latency_ms || 0) + ' ms') : 'Sin respuesta';
                var details = note + (item.sensitive ? ' · Sensible/local' : '');
                var li = document.createElement('li');
                var wrapper = document.createElement('div');
                var strong = document.createElement('strong');
                var subtitle = document.createElement('span');
                var badge = document.createElement('span');

                if (item.url) {
                    var link = document.createElement('a');
                    link.className = 'service-link';
                    link.href = item.url;
                    link.target = '_blank';
                    link.rel = 'noopener noreferrer';
                    link.textContent = item.name || 'Servicio';
                    strong.appendChild(link);
                } else {
                    strong.textContent = item.name || 'Servicio';
                }

                subtitle.textContent = details;
                badge.className = 'badge ' + badgeClass;
                badge.textContent = stateLabel;

                wrapper.appendChild(strong);
                wrapper.appendChild(subtitle);
                li.appendChild(wrapper);
                li.appendChild(badge);
                list.appendChild(li);
            });
        }).catch(function () {
            failures.services += 1;
        }).finally(function () {
            inFlight.services = false;
            scheduleNext('services', 5000);
        });
    }

    function renderResourceValue(item) {
        if (!item || item.status !== 'real' || typeof item.value !== 'number') {
            return 'Pendiente';
        }
        return item.value.toFixed(1) + (item.unit || '');
    }

    function pollResources() {
        if (inFlight.resources) {
            scheduleNext('resources', 10000);
            return;
        }

        inFlight.resources = true;

        fetchJson(endpoints.resources, 2500).then(function (data) {
            failures.resources = 0;
            setText('[data-resource-cpu]', renderResourceValue(data.cpu));
            setText('[data-resource-memory]', renderResourceValue(data.memory));
            setText('[data-resource-storage]', renderResourceValue(data.storage));

            if (data.state === 'real') {
                setText('[data-resource-note]', 'Métricas reales básicas visibles desde el contenedor. Fuente declarada por API.');
            } else {
                setText('[data-resource-note]', 'Métricas reales básicas visibles desde el contenedor. Fuente declarada por API.');
            }
        }).catch(function () {
            failures.resources += 1;
        }).finally(function () {
            inFlight.resources = false;
            scheduleNext('resources', 10000);
        });
    }

    function pollContainers() {
        if (inFlight.containers) {
            scheduleNext('containers', 10000);
            return;
        }

        inFlight.containers = true;

        fetchJson(endpoints.containers, 2500).then(function (data) {
            failures.containers = 0;

            var total = data.containers_total;
            var running = data.containers_running;
            var exited = data.containers_exited;
            var freshness = data.freshness;
            var hasValidCounts = typeof total === 'number' && typeof running === 'number' && typeof exited === 'number';

            if (freshness === 'fresh' && hasValidCounts) {
                var label = running + '/' + total + ' activos';

                if (exited > 0) {
                    label += ' · ' + exited + (exited === 1 ? ' parado' : ' parados');
                }

                setText('[data-status-containers]', label);
                return;
            }

            if (freshness === 'stale' && hasValidCounts) {
                setText('[data-status-containers]', running + '/' + total + ' activos · datos antiguos');
                return;
            }

            setText('[data-status-containers]', 'Sin datos');
        }).catch(function () {
            failures.containers += 1;
            setText('[data-status-containers]', 'Sin datos');
        }).finally(function () {
            inFlight.containers = false;
            scheduleNext('containers', 10000);
        });
    }

    function pollTasks() {
        if (inFlight.tasks) {
            scheduleNext('tasks', 30000);
            return;
        }

        inFlight.tasks = true;

        fetchJson(endpoints.tasks, 2500).then(function (data) {
            if (!data || data.ok !== true) {
                throw new Error('Tasks API failed');
            }

            failures.tasks = 0;
            showTaskError(false);
            renderTasks(data.data);
        }).catch(function () {
            failures.tasks += 1;
            showTaskError(true);
        }).finally(function () {
            inFlight.tasks = false;
            scheduleNext('tasks', 30000);
        });
    }

    function pollManuals() {
        if (inFlight.manuals) {
            scheduleNext('manuals', 60000);
            return;
        }

        inFlight.manuals = true;

        fetchJson(endpoints.manuals, 2500).then(function (data) {
            if (!data || data.ok !== true) {
                throw new Error('Manuals API failed');
            }

            failures.manuals = 0;
            showManualsError(false);
            renderManuals(data.data);
            routeTo(window.location.hash);
        }).catch(function () {
            failures.manuals += 1;
            showManualsError(true);
            setText('[data-manuals-state]', 'Sin datos');
        }).finally(function () {
            inFlight.manuals = false;
            scheduleNext('manuals', 60000);
        });
    }

    function pollOperations() {
        if (inFlight.operations) {
            scheduleNext('operations', 60000);
            return;
        }

        inFlight.operations = true;

        fetchJson(endpoints.operations, 2500).then(function (data) {
            if (!data || data.ok !== true || !data.data || data.data.operations_mode !== 'read_only') {
                throw new Error('Operations API failed');
            }

            failures.operations = 0;
            renderOperations(data.data);
        }).catch(function () {
            failures.operations += 1;
            var grid = select('[data-operations-grid]');
            if (grid) {
                grid.textContent = '';
                var article = document.createElement('article');
                var title = document.createElement('h3');
                var text = document.createElement('p');
                article.className = 'operations-card';
                title.textContent = 'Diagnóstico no disponible';
                text.textContent = 'No se pudo cargar el estado operativo read-only.';
                article.appendChild(title);
                article.appendChild(text);
                grid.appendChild(article);
            }
        }).finally(function () {
            inFlight.operations = false;
            scheduleNext('operations', 60000);
        });
    }

    document.addEventListener('click', function (event) {
        var button = event.target.closest('[data-manual-slug]');
        if (!button) {
            return;
        }

        event.preventDefault();
        window.location.hash = '#manuales/' + encodeURIComponent(button.dataset.manualSlug);
        loadManual(button.dataset.manualSlug);
    });

    window.addEventListener('hashchange', function () {
        routeTo(window.location.hash);
    });

    renderClock();
    setInterval(renderClock, 1000);
    routeTo(window.location.hash);

    pollStatus();
    pollServices();
    pollResources();
    pollContainers();
    pollTasks();
    pollManuals();
    pollOperations();
})();

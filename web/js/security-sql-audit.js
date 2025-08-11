/**
 * SQL Security Audit - JavaScript funkcionalita
 * Fakturační systém - Security modul
 */

document.addEventListener('DOMContentLoaded', function() {
    const runAuditBtn = document.getElementById('runAuditBtn');
    const auditLoading = document.getElementById('auditLoading');
    const auditIntro = document.getElementById('auditIntro');
    const auditResults = document.getElementById('auditResults');
    const auditError = document.getElementById('auditError');
    
    // Zkontroluj zda elementy existují
    if (!runAuditBtn) {
        console.error('SQL Audit: Tlačítko runAuditBtn nebylo nalezeno');
        return;
    }
    
    // Event listener pro tlačítko
    runAuditBtn.addEventListener('click', function() {
        runSqlAudit();
    });
    
    /**
     * Spustí SQL security audit
     */
    function runSqlAudit() {
        // Skryj všechny sekce
        hideAllSections();
        showLoadingSection();
        
        // Deaktivuj tlačítko
        disableButton();
        
        // Progress bar animace
        startProgressAnimation();
        
        // AJAX volání
        const url = runAuditBtn.getAttribute('data-url');
        if (!url) {
            console.error('SQL Audit: URL pro audit není definované');
            displayAuditError('Chyba konfigurace: URL pro audit není definované');
            return;
        }
        
        fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            stopProgressAnimation();
            
            setTimeout(() => {
                hideLoadingSection();
                
                if (data.success) {
                    displayAuditResults(data.results);
                } else {
                    displayAuditError(data.error || 'Neznámá chyba při zpracování auditu');
                }
                
                enableButton();
            }, 500);
        })
        .catch(error => {
            console.error('SQL Audit Error:', error);
            stopProgressAnimation();
            hideLoadingSection();
            displayAuditError('Nastala chyba při komunikaci se serverem: ' + error.message);
            enableButton();
        });
    }
    
    /**
     * Skryje všechny sekce
     */
    function hideAllSections() {
        if (auditIntro) auditIntro.style.display = 'none';
        if (auditResults) auditResults.style.display = 'none';
        if (auditError) auditError.style.display = 'none';
    }
    
    /**
     * Zobrazí loading sekci
     */
    function showLoadingSection() {
        if (auditLoading) auditLoading.style.display = 'block';
    }
    
    /**
     * Skryje loading sekci
     */
    function hideLoadingSection() {
        if (auditLoading) auditLoading.style.display = 'none';
    }
    
    /**
     * Deaktivuje tlačítko a změní text
     */
    function disableButton() {
        runAuditBtn.disabled = true;
        runAuditBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Spouštím audit...';
    }
    
    /**
     * Aktivuje tlačítko a obnoví text
     */
    function enableButton() {
        runAuditBtn.disabled = false;
        runAuditBtn.innerHTML = '<i class="bi bi-arrow-clockwise me-2"></i>Spustit znovu';
    }
    
    let progressInterval;
    
    /**
     * Spustí animaci progress baru
     */
    function startProgressAnimation() {
        const progressBar = document.getElementById('auditProgress');
        if (!progressBar) return;
        
        let progress = 0;
        progressInterval = setInterval(() => {
            progress += Math.random() * 15;
            if (progress > 90) progress = 90;
            progressBar.style.width = progress + '%';
        }, 300);
    }
    
    /**
     * Zastaví animaci progress baru a dokončí ji
     */
    function stopProgressAnimation() {
        if (progressInterval) {
            clearInterval(progressInterval);
        }
        
        const progressBar = document.getElementById('auditProgress');
        if (progressBar) {
            progressBar.style.width = '100%';
        }
    }
    
    /**
     * Zobrazí výsledky auditu
     * @param {Object} results - Výsledky auditu
     */
    function displayAuditResults(results) {
        if (!auditResults) return;
        
        auditResults.style.display = 'block';
        
        const summary = results.summary;
        if (!summary) {
            displayAuditError('Chyba: Neúplná data z auditu');
            return;
        }
        
        // Vyplnění základních statistik
        updateElement('filesScanned', summary.files_scanned);
        updateElement('queriesFound', summary.total_queries);
        updateElement('safeQueries', summary.safe_queries);
        updateElement('potentialIssues', summary.potential_issues);
        updateElement('safetyPercentage', summary.safety_percentage + '%');
        
        // Overall status badge
        updateStatusBadge(summary.overall_status);
        
        // Zobrazení jednotlivých sekcí
        if (summary.priority_issues && summary.priority_issues.length > 0) {
            displayPriorityIssues(summary.priority_issues);
        }
        
        if (results.potential_issues && results.potential_issues.length > 0) {
            displayAllIssues(results.potential_issues);
        }
        
        if (results.safe_queries && results.safe_queries.length > 0) {
            displaySafeQueries(results.safe_queries);
        }
        
        if (results.recommendations && results.recommendations.length > 0) {
            displayRecommendations(results.recommendations);
        }
    }
    
    /**
     * Aktualizuje textový obsah elementu
     * @param {string} elementId 
     * @param {string|number} value 
     */
    function updateElement(elementId, value) {
        const element = document.getElementById(elementId);
        if (element) {
            element.textContent = value || '-';
        }
    }
    
    /**
     * Aktualizuje status badge
     * @param {string} status 
     */
    function updateStatusBadge(status) {
        const statusBadge = document.getElementById('overallStatus');
        if (!statusBadge) return;
        
        const statusConfig = {
            'EXCELLENT': { class: 'bg-success', text: 'VÝBORNÉ' },
            'GOOD': { class: 'bg-info', text: 'DOBRÉ' },
            'NEEDS_ATTENTION': { class: 'bg-warning', text: 'VYŽADUJE POZORNOST' },
            'CRITICAL': { class: 'bg-danger', text: 'KRITICKÉ' }
        };
        
        const config = statusConfig[status] || { class: 'bg-secondary', text: 'NEZNÁMÉ' };
        statusBadge.className = 'badge security-status-badge ' + config.class;
        statusBadge.textContent = config.text;
    }
    
    /**
     * Zobrazí prioritní problémy
     * @param {Array} issues 
     */
    function displayPriorityIssues(issues) {
        const section = document.getElementById('priorityIssuesSection');
        const list = document.getElementById('priorityIssuesList');
        
        if (!section || !list) return;
        
        list.innerHTML = '';
        issues.forEach((issue) => {
            const severity = issue.safety_score < 3 ? 'danger' : 'warning';
            const severityText = issue.safety_score < 3 ? 'KRITICKÉ' : 'STŘEDNÍ';
            
            const issueElement = createIssueElement(issue, severity, severityText, true);
            list.appendChild(issueElement);
        });
        
        section.style.display = 'block';
    }
    
    /**
     * Zobrazí všechny problémy
     * @param {Array} issues 
     */
    function displayAllIssues(issues) {
        const section = document.getElementById('allIssuesSection');
        const list = document.getElementById('allIssuesList');
        
        if (!section || !list) return;
        
        list.innerHTML = '';
        issues.forEach((issue) => {
            const severity = issue.safety_score < 3 ? 'danger' : (issue.safety_score < 6 ? 'warning' : 'info');
            const issueElement = createIssueElement(issue, severity, null, false);
            list.appendChild(issueElement);
        });
        
        section.style.display = 'block';
    }
    
    /**
     * Zobrazí bezpečné dotazy
     * @param {Array} queries 
     */
    function displaySafeQueries(queries) {
        const section = document.getElementById('safeQueriesSection');
        const list = document.getElementById('safeQueriesList');
        
        if (!section || !list) return;
        
        list.innerHTML = '';
        queries.forEach((query) => {
            const queryElement = createSafeQueryElement(query);
            list.appendChild(queryElement);
        });
        
        section.style.display = 'block';
    }
    
    /**
     * Zobrazí doporučení
     * @param {Array} recommendations 
     */
    function displayRecommendations(recommendations) {
        const list = document.getElementById('recommendationsList');
        if (!list) return;
        
        const ul = document.createElement('ul');
        ul.className = 'list-unstyled';
        
        recommendations.forEach(rec => {
            const li = document.createElement('li');
            const isUrgent = rec.includes('URGENT') || rec.includes('okamžitě');
            li.className = isUrgent ? 'text-danger fw-bold mb-2' : 'text-dark mb-2';
            li.innerHTML = '<i class="bi bi-lightbulb me-2"></i>' + escapeHtml(rec);
            ul.appendChild(li);
        });
        
        list.innerHTML = '';
        list.appendChild(ul);
    }
    
    /**
     * Vytvoří element pro issue
     * @param {Object} issue 
     * @param {string} severity 
     * @param {string} severityText 
     * @param {boolean} detailed 
     */
    function createIssueElement(issue, severity, severityText, detailed) {
        const div = document.createElement('div');
        div.className = `security-issue-item ${severity}`;
        
        let content = `
            <div class="security-issue-header">
                <div class="security-issue-file">
                    📁 ${escapeHtml(issue.file)}:${issue.line}
                </div>
                <span class="badge bg-${severity} security-issue-score">
                    Skóre: ${issue.safety_score}/10
                </span>
            </div>`;
        
        if (severityText) {
            content += `
                <div class="mb-2">
                    <span class="badge bg-${severity}">${severityText}</span>
                </div>`;
        }
        
        if (issue.matched_text) {
            content += `
                <div class="security-issue-code">
                    ${escapeHtml(issue.matched_text)}
                </div>`;
        }
        
        if (detailed && issue.issues) {
            content += `
                <div class="security-issue-details">
                    <strong>Problémy:</strong>
                    <ul class="mb-2">
                        ${issue.issues.map(i => `<li>${escapeHtml(i)}</li>`).join('')}
                    </ul>
                </div>`;
        }
        
        if (detailed && issue.recommendations) {
            content += `
                <div class="security-issue-details">
                    <strong>Doporučení:</strong>
                    <ul class="mb-0">
                        ${issue.recommendations.map(r => `<li>${escapeHtml(r)}</li>`).join('')}
                    </ul>
                </div>`;
        }
        
        div.innerHTML = content;
        return div;
    }
    
    /**
     * Vytvoří element pro bezpečný dotaz
     * @param {Object} query 
     */
    function createSafeQueryElement(query) {
        const div = document.createElement('div');
        div.className = 'security-issue-item safe';
        
        div.innerHTML = `
            <div class="security-issue-header">
                <div class="security-issue-file">
                    📁 ${escapeHtml(query.file)}:${query.line}
                </div>
                <span class="badge bg-success security-issue-score">
                    Skóre: ${query.safety_score}/10
                </span>
            </div>
            <div class="security-issue-code">
                ${escapeHtml(query.query || query.matched_text || 'N/A')}
            </div>`;
        
        return div;
    }
    
    /**
     * Zobrazí chybu auditu
     * @param {string} errorMessage 
     */
    function displayAuditError(errorMessage) {
        if (!auditError) return;
        
        auditError.style.display = 'block';
        const errorElement = document.getElementById('auditErrorMessage');
        if (errorElement) {
            errorElement.textContent = errorMessage;
        }
    }
    
    /**
     * Escapuje HTML znaky
     * @param {string} text 
     * @returns {string}
     */
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
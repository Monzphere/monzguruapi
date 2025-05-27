/**
 * MonzGuru API Module JavaScript
 * Initialization and configuration of the MonzGuru API module within Zabbix
 */

// Wait for the DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
    // Wait a bit to ensure Zabbix has loaded completely
    setTimeout(function() {
        initializeZapixModule();
    }, 100);
});

function initializeZapixModule() {

    // Initialize event listeners
    initializeEventListeners();
    
    // Initialize typeahead for autocomplete
    initializeTypeahead();
    
    // Initialize the connection manager
    initializeConnectionManager();
    
    // Update the connection status
    updateConnectionStatus();
}



function initializeEventListeners() {
    // Execute button
    const executeBtn = document.getElementById('execute');
    if (executeBtn) {
        executeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            executeApiCall();
        });
    } else {
        console.error('Execute button not found!');
    }
    

    
    // Save button
    const saveBtn = document.getElementById('saveRequest');
    if (saveBtn) {
        saveBtn.addEventListener('click', saveRequest);
    }
    
    // Load button
    const loadBtn = document.getElementById('loadRequest');
    if (loadBtn) {
        loadBtn.addEventListener('click', loadRequest);
    }
    
    // Example button
    const exampleBtn = document.getElementById('loadExample');
    if (exampleBtn) {
        exampleBtn.addEventListener('click', loadExample);
    }
    
    // JSONPath test button
    const jsonPathBtn = document.getElementById('testJsonPath');
    if (jsonPathBtn) {
        jsonPathBtn.addEventListener('click', testJsonPath);
    }
}

function initializeTypeahead() {
    const methodInput = document.getElementById('apimethod');
    if (methodInput && typeof methods !== 'undefined' && typeof zabbixVersion !== 'undefined') {
        try {
            // Basic autocomplete implementation
            methodInput.addEventListener('input', function() {
                const value = this.value.toLowerCase();
                const suggestions = methods[zabbixVersion] || [];
                const filtered = suggestions.filter(method => 
                    method.toLowerCase().includes(value)
                ).slice(0, 10);
                
                showSuggestions(this, filtered);
            });
        } catch (e) {
            console.error('Error initializing typeahead:', e);
        }
    }
}

function showSuggestions(input, suggestions) {
    // Remove previous suggestions
    const existingSuggestions = document.querySelector('.mnzguruapi-suggestions');
    if (existingSuggestions) {
        existingSuggestions.remove();
    }
    
    if (suggestions.length === 0) return;
    
    // Create suggestions container
    const suggestionsDiv = document.createElement('div');
    suggestionsDiv.className = 'mnzguruapi-suggestions';
    suggestionsDiv.style.cssText = `
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ccc;
        border-top: none;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    `;
    
    suggestions.forEach(suggestion => {
        const item = document.createElement('div');
        item.textContent = suggestion;
        item.style.cssText = `
            padding: 8px 12px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
        `;
        
        item.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#0088cc';
            this.style.color = 'white';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
            this.style.color = '';
        });
        
        item.addEventListener('click', function() {
            input.value = suggestion;
            suggestionsDiv.remove();
        });
        
        suggestionsDiv.appendChild(item);
    });
    
    // Position relative to input
    input.parentNode.style.position = 'relative';
    input.parentNode.appendChild(suggestionsDiv);
    
    // Remove suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!input.contains(e.target) && !suggestionsDiv.contains(e.target)) {
            suggestionsDiv.remove();
        }
    }, { once: true });
}

function initializeConnectionManager() {
    // Load saved connections from localStorage
    const savedConnections = localStorage.getItem('mnzguruapi_connections');
    if (savedConnections) {
        try {
            window.mnzguruapiConnections = JSON.parse(savedConnections);
        } catch (e) {
            window.mnzguruapiConnections = {};
        }
    } else {
        window.mnzguruapiConnections = {};
    }
}

function executeApiCall() {

    const method = document.getElementById('apimethod').value;
    const paramsTextarea = document.getElementById('apiparams');
    const params = paramsTextarea ? paramsTextarea.value : '{}';
    if (!method) {
        alert('Please enter an API method');
        return;
    }
    
    // Validate JSON
    try {
        JSON.parse(params);
    } catch (e) {
        alert('Invalid JSON parameters: ' + e.message);
        return;
    }
    
    // Clear response areas and add loading
    const requestArea = document.getElementById('request');
    const responseArea = document.getElementById('response');
    const executeBtn = document.getElementById('execute');
    
    if (executeBtn) {
        executeBtn.disabled = true;
        executeBtn.innerHTML = 'Executing...';
        executeBtn.classList.add('mnzguruapi-loading');
        // Temporarily remove icon during loading
        executeBtn.style.setProperty('--before-content', '""');
    }
    
    if (requestArea) requestArea.textContent = 'Preparing request...';
    if (responseArea) responseArea.textContent = 'Waiting for response...';
    
    // Create the request
    const request = {
        jsonrpc: "2.0",
        method: method,
        params: JSON.parse(params || '{}'),
        id: 1
    };
    
    // Show the request
    if (requestArea) {
        requestArea.textContent = JSON.stringify(request, null, 2);
    }
    
    // Make the real call to the controller
    const formData = new FormData();
    formData.append('action', 'mnzguruapi.view');
    formData.append('method', method);
    formData.append('params', params);
    
    // Add CSRF token if available
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (csrfToken) {
        formData.append('_token', csrfToken.getAttribute('content'));
    }
    
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Failed to parse JSON:', e);
                throw new Error('Invalid JSON response: ' + text.substring(0, 200));
            }
        });
    })
    .then(data => {
        if (responseArea) {
            responseArea.textContent = JSON.stringify(data, null, 2);
        }
    })
    .catch(error => {
        console.error('Error in API call:', error);
        if (responseArea) {
            responseArea.textContent = JSON.stringify({
                jsonrpc: "2.0",
                error: {
                    code: -1,
                    message: "Communication error: " + error.message
                },
                id: 1
            }, null, 2);
        }
    })
    .finally(() => {
        // Remove loading state
        if (executeBtn) {
            executeBtn.disabled = false;
            executeBtn.innerHTML = 'Execute';
            executeBtn.classList.remove('mnzguruapi-loading');
            // Restore icon
            executeBtn.style.removeProperty('--before-content');
        }
    });
}



function saveRequest() {
    const method = document.getElementById('apimethod').value;
    const paramsTextarea = document.getElementById('apiparams');
    const params = paramsTextarea ? paramsTextarea.value : '{}';
    
    if (!method) {
        alert('Please enter an API method');
        return;
    }
    
    const name = prompt('Name to save this request:');
    if (name) {
        const savedRequests = JSON.parse(localStorage.getItem('mnzguruapi_requests') || '{}');
        savedRequests[name] = {
            method: method,
            params: params
        };
        localStorage.setItem('mnzguruapi_requests', JSON.stringify(savedRequests));
        alert('Request saved successfully!');
    }
}

function loadRequest() {
    const savedRequests = JSON.parse(localStorage.getItem('mnzguruapi_requests') || '{}');
    const names = Object.keys(savedRequests);
    
    if (names.length === 0) {
        alert('No saved requests found');
        return;
    }
    
    const name = prompt('Choose a request to load:\n' + names.join('\n'));
    if (name && savedRequests[name]) {
        document.getElementById('apimethod').value = savedRequests[name].method;
        const paramsTextarea = document.getElementById('apiparams');
        if (paramsTextarea) {
            paramsTextarea.value = savedRequests[name].params;
        }
    }
}

function updateConnectionStatus() {
    const connInfo = document.getElementById('connInfo');
    
    if (connInfo) {
        connInfo.textContent = 'Connected via Internal API';
    }
}

function loadExample() {
    const examples = [
        {
            name: 'List hosts',
            method: 'host.get',
            params: '{\n    "output": ["hostid", "host", "name", "status"],\n    "limit": 10\n}',
            jsonpath: 'result[*].host'
        },
        {
            name: 'Search problems',
            method: 'problem.get',
            params: '{\n    "output": "extend",\n    "recent": true,\n    "limit": 20\n}',
            jsonpath: 'result[*].name'
        },
        {
            name: 'List host groups',
            method: 'hostgroup.get',
            params: '{\n    "output": ["groupid", "name"],\n    "sortfield": "name"\n}',
            jsonpath: 'result[*].name'
        },
        {
            name: 'Get API version',
            method: 'apiinfo.version',
            params: '{}',
            jsonpath: 'result'
        },
        {
            name: 'List users',
            method: 'user.get',
            params: '{\n    "output": ["userid", "username", "name", "surname"],\n    "getAccess": true\n}',
            jsonpath: 'result[*].username'
        },
        {
            name: 'Search items',
            method: 'item.get',
            params: '{\n    "output": ["itemid", "name", "key_", "lastvalue"],\n    "monitored": true,\n    "limit": 15\n}',
            jsonpath: 'result[*].key_'
        },
        {
            name: 'List templates',
            method: 'template.get',
            params: '{\n    "output": ["templateid", "host", "name"],\n    "sortfield": "name"\n}',
            jsonpath: 'result[*].name'
        }
    ];
    
    const exampleNames = examples.map((ex, index) => `${index + 1}. ${ex.name}`).join('\n');
    const choice = prompt('Choose an example:\n\n' + exampleNames + '\n\nEnter the number:');
    
    if (choice && !isNaN(choice)) {
        const index = parseInt(choice) - 1;
        if (index >= 0 && index < examples.length) {
            const example = examples[index];
            document.getElementById('apimethod').value = example.method;
            const paramsTextarea = document.getElementById('apiparams');
            if (paramsTextarea) {
                paramsTextarea.value = example.params;
            }
            
            // Load suggested JSONPath if available
            const jsonPathInput = document.getElementById('jsonpath');
            if (jsonPathInput && example.jsonpath) {
                jsonPathInput.value = example.jsonpath;
            }
        } else {
            alert('Invalid number!');
        }
    }
}

function testJsonPath() {
    const jsonPathInput = document.getElementById('jsonpath');
    const responseArea = document.getElementById('response');
    const resultArea = document.getElementById('jsonpath-result');
    const outputArea = document.getElementById('jsonpath-output');
    
    if (!jsonPathInput || !responseArea || !resultArea || !outputArea) {
        alert('Required elements not found');
        return;
    }
    
    const jsonPath = jsonPathInput.value.trim();
    const responseText = responseArea.textContent;
    
    if (!jsonPath) {
        alert('Please enter a JSONPath');
        return;
    }
    
    if (!responseText || responseText === 'Waiting for response...') {
        alert('Execute an API query first to have data to test');
        return;
    }
    
    try {
        // Parse JSON response
        const responseData = JSON.parse(responseText);
        
        // Basic JSONPath implementation
        const result = evaluateJsonPath(responseData, jsonPath);
        
        // Show result
        outputArea.textContent = JSON.stringify(result, null, 2);
        resultArea.classList.remove('hidden');
        
        // Scroll to result
        resultArea.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        
    } catch (error) {
        alert('Error processing JSONPath: ' + error.message);
        console.error('JSONPath error:', error);
    }
}

function evaluateJsonPath(data, path) {
    
    // Remove initial $ if present
    path = path.replace(/^\$\.?/, '');
    
    if (!path) {
        return data;
    }
    
    // Handle special cases first
    if (path.includes('[*]')) {
        // Example: result[*].host
        const beforeWildcard = path.split('[*]')[0];
        const afterWildcard = path.split('[*]')[1];
        
        let current = data;
        
        // Navigate to array
        if (beforeWildcard) {
            const beforeParts = beforeWildcard.split('.').filter(part => part !== '');
            for (let part of beforeParts) {
                if (current && typeof current === 'object') {
                    current = current[part];
                } else {
                    throw new Error(`Property '${part}' not found`);
                }
            }
        }
        
        // Check if it's an array
        if (!Array.isArray(current)) {
            throw new Error('Wildcard [*] used on non-array value');
        }
        
        // Apply the rest of the path to each item
        if (afterWildcard && afterWildcard.startsWith('.')) {
            const property = afterWildcard.substring(1);
            return current.map(item => {
                if (item && typeof item === 'object' && property in item) {
                    return item[property];
                }
                return undefined;
            }).filter(item => item !== undefined);
        } else {
            return current;
        }
    }
    
    // Split path into parts for normal cases
    const parts = path.split('.').filter(part => part !== '');
    let current = data;
    
    for (let i = 0; i < parts.length; i++) {
        let part = parts[i];
        
        // Handle array indices [0], [1], etc.
        if (part.includes('[') && part.includes(']')) {
            const property = part.split('[')[0];
            const indexMatch = part.match(/\[(\d+)\]/);
            
            if (property) {
                // First access the property
                if (current && typeof current === 'object' && property in current) {
                    current = current[property];
                } else {
                    throw new Error(`Property '${property}' not found`);
                }
            }
            
            if (indexMatch) {
                // Then access the index
                const index = parseInt(indexMatch[1]);
                if (Array.isArray(current)) {
                    current = current[index];
                } else {
                    throw new Error(`Index [${index}] used on non-array value`);
                }
            }
        } else {
            // Simple property
            if (current && typeof current === 'object' && part in current) {
                current = current[part];
            } else {
                throw new Error(`Property '${part}' not found in path '${path}'`);
            }
        }
        
        if (current === undefined) {
            throw new Error(`Path not found: ${path}`);
        }
    }
    
    return current;
}

// Export functions for global use if needed
window.ZapixModule = {
    executeApiCall,
    saveRequest,
    loadRequest,
    loadExample,
    updateConnectionStatus,
    testJsonPath
}; 
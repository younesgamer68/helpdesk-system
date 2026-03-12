(function() {
    // Inject CSS
    var style = document.createElement('style');
    style.innerHTML = `
        #helpdesk-kb-widget {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 999999;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        #helpdesk-kb-widget-btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: #0d9488; /* teal-600 */
            color: #fff;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s;
        }
        #helpdesk-kb-widget-btn:hover {
            transform: scale(1.05);
        }
        #helpdesk-kb-widget-container {
            display: none;
            position: absolute;
            bottom: 80px;
            right: 0;
            width: 350px;
            max-height: 500px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
            overflow: hidden;
            border: 1px solid #e5e7eb;
            flex-direction: column;
        }
        #helpdesk-kb-widget-header {
            background: #0d9488;
            color: #fff;
            padding: 16px;
            font-weight: 600;
            border-bottom: 1px solid #0f766e;
        }
        #helpdesk-kb-widget-body {
            padding: 16px;
            overflow-y: auto;
            max-height: 380px;
        }
        #helpdesk-kb-widget-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            margin-bottom: 12px;
            box-sizing: border-box;
        }
        #helpdesk-kb-widget-results {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        #helpdesk-kb-widget-results li {
            margin-bottom: 8px;
            border-bottom: 1px solid #f3f4f6;
            padding-bottom: 8px;
        }
        #helpdesk-kb-widget-results a {
            color: #0d9488;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
        }
        #helpdesk-kb-widget-results a:hover {
            text-decoration: underline;
        }
        #helpdesk-kb-widget-results p {
            font-size: 12px;
            color: #6b7280;
            margin: 4px 0 0;
        }
        #helpdesk-kb-widget-footer {
            padding: 12px 16px;
            text-align: center;
            border-top: 1px solid #f3f4f6;
            background: #f9fafb;
            font-size: 12px;
        }
        #helpdesk-kb-widget-footer a {
            color: #4b5563;
            text-decoration: none;
        }
        #helpdesk-kb-widget-footer a:hover {
            text-decoration: underline;
        }
    `;
    document.head.appendChild(style);

    // Create Widget DOM
    var wrapper = document.createElement('div');
    wrapper.id = 'helpdesk-kb-widget';
    
    var container = document.createElement('div');
    container.id = 'helpdesk-kb-widget-container';
    
    container.innerHTML = \`
        <div id="helpdesk-kb-widget-header">{{ $company->name }} Support</div>
        <div id="helpdesk-kb-widget-body">
            <input type="text" id="helpdesk-kb-widget-input" placeholder="Search for answers..." />
            <div id="helpdesk-kb-widget-loading" style="display:none; text-align:center; padding: 20px; color:#6b7280; font-size:12px;">Searching...</div>
            <ul id="helpdesk-kb-widget-results">
                <li style="color:#6b7280; font-size:13px;">Type a question to search our knowledge base.</li>
            </ul>
        </div>
        <div id="helpdesk-kb-widget-footer">
            <a href="{{ $portalUrl }}" target="_blank">Visit Full Knowledge Base &rarr;</a>
        </div>
    \`;
    
    var btn = document.createElement('button');
    btn.id = 'helpdesk-kb-widget-btn';
    btn.innerHTML = '<svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
    
    wrapper.appendChild(container);
    wrapper.appendChild(btn);
    document.body.appendChild(wrapper);

    // Logic
    var isOpen = false;
    btn.addEventListener('click', function() {
        isOpen = !isOpen;
        container.style.display = isOpen ? 'flex' : 'none';
        if (isOpen) {
            document.getElementById('helpdesk-kb-widget-input').focus();
        }
    });

    var input = document.getElementById('helpdesk-kb-widget-input');
    var resultsList = document.getElementById('helpdesk-kb-widget-results');
    var loading = document.getElementById('helpdesk-kb-widget-loading');
    var typingTimer;                
    var doneTypingInterval = 500;  

    input.addEventListener('keyup', function () {
        clearTimeout(typingTimer);
        var q = input.value.trim();
        if (q.length > 2) {
            loading.style.display = 'block';
            resultsList.innerHTML = '';
            typingTimer = setTimeout(function() { search(q); }, doneTypingInterval);
        } else {
            resultsList.innerHTML = '<li style="color:#6b7280; font-size:13px;">Type a question to search our knowledge base.</li>';
        }
    });

    function search(query) {
        fetch('{{ $apiUrl }}?q=' + encodeURIComponent(query))
            .then(res => res.json())
            .then(data => {
                loading.style.display = 'none';
                resultsList.innerHTML = '';
                var articles = data.data;
                if (articles && articles.length > 0) {
                    articles.forEach(function(article) {
                        var li = document.createElement('li');
                        var url = '{{ $portalUrl }}/article/' + article.slug;
                        var desc = article.meta_description ? article.meta_description : '';
                        if(!desc && article.body) {
                            var tmp = document.createElement("DIV");
                            tmp.innerHTML = article.body;
                            desc = tmp.textContent || tmp.innerText || "";
                            desc = desc.substring(0, 80) + '...';
                        }
                        li.innerHTML = '<a href="'+url+'" target="_blank">' + escapeHtml(article.title) + '</a><p>' + escapeHtml(desc) + '</p>';
                        resultsList.appendChild(li);
                    });
                } else {
                    resultsList.innerHTML = '<li style="color:#6b7280; font-size:13px;">No results found.</li>';
                }
            })
            .catch(err => {
                loading.style.display = 'none';
                resultsList.innerHTML = '<li style="color:#ef4444; font-size:13px;">Error loading results.</li>';
            });
    }

    function escapeHtml(unsafe) {
        return (unsafe || '').toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
})();

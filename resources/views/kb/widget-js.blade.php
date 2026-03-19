(function () {
var companyName = @json($company->name);
var portalUrl = @json($portalUrl);
var apiUrl = @json($apiUrl);
var logoUrl = @json($logoUrl);
var scriptTag = document.currentScript;

if (!scriptTag) {
var allScripts = document.getElementsByTagName('script');
for (var i = allScripts.length - 1; i >= 0; i--) {
if (allScripts[i].src && allScripts[i].src.indexOf('/kb/widget.js') !== -1) {
scriptTag = allScripts[i];
break;
}
}
}

var articleBaseOverride = scriptTag ? (scriptTag.getAttribute('data-article-base-url') || '') : '';
var defaultLinkMode = scriptTag ? (scriptTag.getAttribute('data-default-link-mode') || 'portal') : 'portal';
var articleBaseUrl = defaultLinkMode === 'custom' && articleBaseOverride
? articleBaseOverride.replace(/\/+$/, '')
: (portalUrl + '/article');

var openInNewTabAttr = scriptTag ? scriptTag.getAttribute('data-open-in-new-tab') : null;
var openInNewTab = openInNewTabAttr !== 'false';

var style = document.createElement('style');
style.textContent = `
#helpdesk-kb-widget {
position: fixed;
bottom: 20px;
right: 20px;
z-index: 2147483647;
font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
}

#helpdesk-kb-widget-btn {
width: 60px;
height: 60px;
border: 0;
border-radius: 9999px;
background: #0d9488;
color: #fff;
cursor: pointer;
display: flex;
align-items: center;
justify-content: center;
box-shadow: 0 10px 24px rgba(0, 0, 0, 0.22);
transition: transform 0.18s ease;
}

#helpdesk-kb-widget-btn:hover {
transform: scale(1.05);
}

#helpdesk-kb-widget-panel {
display: none;
position: absolute;
right: 0;
bottom: 78px;
width: 350px;
max-height: 500px;
background: #fff;
border: 1px solid #e5e7eb;
border-radius: 12px;
box-shadow: 0 10px 30px rgba(0, 0, 0, 0.16);
overflow: hidden;
flex-direction: column;
}

#helpdesk-kb-widget-header {
background: #0d9488;
color: #fff;
padding: 14px 16px;
font-weight: 600;
border-bottom: 1px solid #0f766e;
display: flex;
align-items: center;
gap: 10px;
}

#helpdesk-kb-widget-header img {
width: 26px;
height: 26px;
border-radius: 5px;
object-fit: contain;
flex-shrink: 0;
background: rgba(255, 255, 255, 0.15);
padding: 2px;
}

#helpdesk-kb-widget-body {
padding: 14px 16px;
max-height: 380px;
overflow-y: auto;
}

#helpdesk-kb-widget-input {
width: 100%;
box-sizing: border-box;
border: 1px solid #d1d5db;
border-radius: 8px;
padding: 10px;
margin-bottom: 12px;
outline: none;
}

#helpdesk-kb-widget-input:focus {
border-color: #0d9488;
box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.18);
}

#helpdesk-kb-widget-loading {
display: none;
text-align: center;
font-size: 12px;
color: #6b7280;
padding: 12px 0;
}

#helpdesk-kb-widget-results {
list-style: none;
margin: 0;
padding: 0;
}

#helpdesk-kb-widget-results li {
border-bottom: 1px solid #f1f5f9;
padding: 8px 0;
}

#helpdesk-kb-widget-results a {
color: #0d9488;
font-size: 14px;
font-weight: 600;
text-decoration: none;
}

#helpdesk-kb-widget-results a:hover {
text-decoration: underline;
}

#helpdesk-kb-widget-results p {
margin: 4px 0 0;
font-size: 12px;
color: #64748b;
}

#helpdesk-kb-widget-footer {
background: #f8fafc;
border-top: 1px solid #f1f5f9;
text-align: center;
padding: 10px 14px;
font-size: 12px;
}

#helpdesk-kb-widget-footer a {
color: #475569;
text-decoration: none;
}

#helpdesk-kb-widget-footer a:hover {
text-decoration: underline;
}

@media (max-width: 480px) {
#helpdesk-kb-widget {
right: 12px;
bottom: 12px;
}

#helpdesk-kb-widget-panel {
width: min(92vw, 360px);
right: 0;
}
}
`;
document.head.appendChild(style);

var root = document.createElement('div');
root.id = 'helpdesk-kb-widget';

var panel = document.createElement('div');
panel.id = 'helpdesk-kb-widget-panel';

var header = document.createElement('div');
header.id = 'helpdesk-kb-widget-header';
if (logoUrl) {
var headerLogo = document.createElement('img');
headerLogo.src = logoUrl;
headerLogo.alt = companyName || 'Logo';
header.appendChild(headerLogo);
}
var headerText = document.createElement('span');
headerText.textContent = (companyName || 'Support') + ' Support';
header.appendChild(headerText);

var body = document.createElement('div');
body.id = 'helpdesk-kb-widget-body';

var input = document.createElement('input');
input.id = 'helpdesk-kb-widget-input';
input.type = 'text';
input.placeholder = 'Search for answers...';

var loading = document.createElement('div');
loading.id = 'helpdesk-kb-widget-loading';
loading.textContent = 'Searching...';

var results = document.createElement('ul');
results.id = 'helpdesk-kb-widget-results';

var footer = document.createElement('div');
footer.id = 'helpdesk-kb-widget-footer';

var footerLink = document.createElement('a');
footerLink.href = portalUrl;
footerLink.target = '_blank';
footerLink.rel = 'noopener noreferrer';
footerLink.innerHTML = 'Visit Full Knowledge Base &rarr;';
footer.appendChild(footerLink);

var initialItem = document.createElement('li');
initialItem.style.color = '#6b7280';
initialItem.style.fontSize = '13px';
initialItem.textContent = 'Type at least 3 characters to search.';
results.appendChild(initialItem);

body.appendChild(input);
body.appendChild(loading);
body.appendChild(results);

panel.appendChild(header);
panel.appendChild(body);
panel.appendChild(footer);

var button = document.createElement('button');
button.id = 'helpdesk-kb-widget-btn';
button.type = 'button';
button.setAttribute('aria-label', 'Open knowledge base search');

var ns = 'http://www.w3.org/2000/svg';
var svg = document.createElementNS(ns, 'svg');
svg.setAttribute('width', '24');
svg.setAttribute('height', '24');
svg.setAttribute('fill', 'none');
svg.setAttribute('stroke', 'currentColor');
svg.setAttribute('stroke-width', '2');
svg.setAttribute('viewBox', '0 0 24 24');

var path = document.createElementNS(ns, 'path');
var iconPath = [
'M8.228 9c.549-1.165 2.03-2 3.772-2',
'2.21 0 4 1.343 4 3',
'0 1.4-1.278 2.575-3.006 2.907',
'-.542.104-.994.54-.994 1.093',
'm0 3h.01',
'M21 12a9 9 0 11-18 0',
'9 9 0 0118 0z'
].join(' ');
path.setAttribute('d', iconPath);
svg.appendChild(path);
button.appendChild(svg);

root.appendChild(panel);
root.appendChild(button);
document.body.appendChild(root);

var isOpen = false;
var typingTimer = null;
var debounceMs = 400;

button.addEventListener('click', function () {
isOpen = !isOpen;
panel.style.display = isOpen ? 'flex' : 'none';
if (isOpen) {
input.focus();
}
});

input.addEventListener('keyup', function () {
clearTimeout(typingTimer);
var query = input.value.trim();

if (query.length < 3) { loading.style.display='none' ; setStatus('Type at least 3 characters to search.', '#6b7280' );
    return; } loading.style.display='block' ; results.innerHTML='' ; typingTimer=setTimeout(function () { search(query);
    }, debounceMs); }); function setStatus(message, color) { results.innerHTML='' ; var
    item=document.createElement('li'); item.style.color=color; item.style.fontSize='13px' ; item.textContent=message;
    results.appendChild(item); } function search(query) { fetch(apiUrl + '?q=' + encodeURIComponent(query))
    .then(function (response) { return response.json(); }) .then(function (payload) { loading.style.display='none' ;
    results.innerHTML='' ; var articles=payload && payload.data ? payload.data : []; if (!articles.length) {
    setStatus('No results found.', '#6b7280' ); return; } articles.forEach(function (article) { var
    li=document.createElement('li'); var a=document.createElement('a'); a.href=articleBaseUrl + '/' + article.slug; if
    (openInNewTab) { a.target='_blank' ; a.rel='noopener noreferrer' ; } a.textContent=article.title
    || 'Untitled article' ; var p=document.createElement('p'); var description=article.meta_description || '' ; if
    (!description && article.body) { var temp=document.createElement('div'); temp.innerHTML=article.body;
    description=(temp.textContent || temp.innerText || '' ).trim(); } if (description.length> 90) {
    description = description.substring(0, 90) + '...';
    }
    p.textContent = description;

    li.appendChild(a);
    li.appendChild(p);
    results.appendChild(li);
    });
    })
    .catch(function () {
    loading.style.display = 'none';
    setStatus('Error loading results.', '#ef4444');
    });
    }
    })();

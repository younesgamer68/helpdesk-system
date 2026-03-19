(function () {
var widgetUrl = @json($widgetUrl);
var rootId = 'helpdesk-chatbot-widget-root';

if (document.getElementById(rootId)) {
return;
}

var root = document.createElement('div');
root.id = rootId;
root.style.position = 'fixed';
root.style.right = '24px';
root.style.bottom = '24px';
root.style.width = '400px';
root.style.height = '600px';
root.style.maxWidth = 'calc(100vw - 16px)';
root.style.maxHeight = 'calc(100vh - 16px)';
root.style.zIndex = '2147483647';
root.style.pointerEvents = 'auto';

var iframe = document.createElement('iframe');
iframe.src = widgetUrl;
iframe.title = 'Helpdesk AI Chatbot';
iframe.style.width = '100%';
iframe.style.height = '100%';
iframe.style.border = '0';
iframe.style.borderRadius = '12px';
iframe.style.background = 'transparent';
iframe.setAttribute('loading', 'lazy');
iframe.setAttribute('allow', 'clipboard-write');

root.appendChild(iframe);
document.body.appendChild(root);

var style = document.createElement('style');
style.textContent = '@media (max-width: 480px) { #' + rootId + ' { right: 8px !important; bottom: 8px !important; width: calc(100vw - 16px) !important; height: calc(100vh - 16px) !important; } }';
document.head.appendChild(style);
})();

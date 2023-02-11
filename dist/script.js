/*! Live Notes https://github.com/JMcrafter26/live-notes/ */

/* CONFIGURATION */
var getTimeout = 5000; // 5 seconds
var postTimeout = 1000; // 1 second
var showDebug = true; // Show debug messages in console.
/* END CONFIGURATION */

var textarea = document.getElementById('content');
var printable = document.getElementById('printable');
var content = textarea.value;

// Make the content available to print.
printable.appendChild(document.createTextNode(content));

textarea.focus();
uploadContent();
checkContent();


function uploadContent() {
    // If textarea value changes.
    if (content !== textarea.value) {
        if (showDebug) {console.log('content has changed!');}
        var temp = textarea.value;
        var request = new XMLHttpRequest();

        request.open('POST', window.location.href, true);
        request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
        request.onload = function() {
            if (request.readyState === 4) {
                if (request.status === 200) {
                    if (showDebug) {console.log('content upload successful!');}
                } else {
                    if (showDebug) {console.log('content upload failed!');}
                }

                // Request has ended, check again after 1 second.
                content = temp;
                setTimeout(uploadContent, postTimeout);
            }
        }
        request.onerror = function() {

            // Try again after 1 second.
            setTimeout(uploadContent, postTimeout);
        }
        request.send('text=' + encodeURIComponent(temp));

        // Make the content available to print.
        printable.removeChild(printable.firstChild);
        printable.appendChild(document.createTextNode(temp));
    } else {

        // Content has not changed, check again after 1 second.
        setTimeout(uploadContent, postTimeout);
        
    }
}

function checkContent() {
    if (showDebug) {console.log('checking content...');}
    var request = new XMLHttpRequest();
    request.open('GET', window.location.href + '?raw', true);
    request.onload = function() {
        if (request.readyState === 4) {
            if (request.status === 200) {
                if (content !== request.responseText) {
                    if (showDebug) {console.log('content has changed!');}
                    content = request.responseText;
                    textarea.value = content;
                } else {
                    if (showDebug) {console.log('content has not changed!');}
                }
            } else {
                if (showDebug) {console.log('content check failed!');}
            }
        }
    }
    request.send();
    setTimeout(checkContent, getTimeout);
}


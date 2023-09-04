<!DOCTYPE html>
<html>
<head>
    <title>Chatbot Task</title>
    <link rel="stylesheet" href="{{ Vite::asset('resources/css/chatBot.css') }}">
</head>
<body>
<div class="chat-container">
    <h2>Chatbot</h2>
    <div id="chat-log">
        <p class="user-message">Hello, what can I help you with today?</p>
    </div>
    <form id="chat-form" method="get" >
        <input class="text_box" type="text" id="user-input" name="user-input" placeholder="Type your message..." />
        <input type="submit" value="Send" />
    </form>
</div>
<script type="text/javascript">
    // Retrieve the conversation history from session storage
    let conversation = sessionStorage.getItem("conversation");
    console.log(conversation);
    // Function to display the conversation history
    function displayConversationHistory() {
        var chatLog = document.getElementById('chat-log');
        chatLog.innerHTML = ''; // Clear the chat log

        for (var i = 0; i < conversation.length; i++) {
            var role = conversation[i].role;
            var content = conversation[i].content;
            var messageElement = document.createElement('p');
            messageElement.innerHTML = '<strong>' + role + ':</strong> ' + content;
            chatLog.appendChild(messageElement);
        }
    }

    // Call the displayConversationHistory function to display the conversation
    displayConversationHistory();

    // Get the text field that we're going to track
    var field = document.getElementById('user-input');

    // See if we have an autosave value
    // (this will only happen if the page is accidentally refreshed)
    if (sessionStorage.getItem('autosave')) {
        // Restore the contents of the text field
        field.value = sessionStorage.getItem('autosave');
    }

    // Listen for changes in the text field
    field.addEventListener('change', function() {
        // And save the results into the session storage object
        sessionStorage.setItem('autosave', field.value);
    });
</script>
</body>
</html>

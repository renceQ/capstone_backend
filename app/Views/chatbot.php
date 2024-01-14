<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>QMJ Chat App</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
  <style>
    body {
      background-color: #f8f9fa;
    }

    #chat-box {
      max-height: 400px;
      overflow-y: auto;
    }

    .user-message {
      background-color: #d4edda;
    }

    .response-message {
      background-color: #cce5ff;
    }
  </style>
</head>
<body>

  <div class="container mt-5">
    <div class="row">
      <div class="col-md-6 offset-md-3">
        <div class="card">
          <div class="card-header bg-primary text-white">
            Qmj-images Chat App
          </div>
          <div class="card-body">
            <div id="chat-box" class="mb-3"></div>
            <form id="chat-form">
              <div class="form-row">
                <div class="col-9">
                  <input type="text" id="message-input" class="form-control" placeholder="Type your message" required>
                </div>
                <div class="col-3">
                  <button type="submit" class="btn btn-success btn-block">Send</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS and Popper.js -->
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
  <!-- Custom JavaScript -->
  <script>
    document.getElementById('chat-form').addEventListener('submit', function (event) {
      event.preventDefault();

      // Get the user's message
      var userMessage = document.getElementById('message-input').value;

      // Clear the input field
      document.getElementById('message-input').value = '';

      // Display the user's message in the chat box
      displayMessage('user-message', userMessage);

      // Replace 'YOUR_API_KEY' with your actual OpenAI GPT API key
      var apiKey = '';

      // Make a POST request to OpenAI GPT API with the user's message
      fetch('https://api.openai.com/v1/chat/completions', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer ' + apiKey,
        },
        body: JSON.stringify({
          messages: [{ role: 'user', content: userMessage }],
        }),
      })
      .then(response => response.json())
      .then(data => {
        // Display the response in the chat box
        var aiResponse = data.choices[0].message.content;
        displayMessage('response-message', aiResponse);
      })
      .catch(error => console.error('Error:', error));
    });

    function displayMessage(className, message) {
      var chatBox = document.getElementById('chat-box');
      var messageElement = document.createElement('div');
      messageElement.className = 'p-2 mb-2 rounded ' + className;
      messageElement.textContent = message;
      chatBox.appendChild(messageElement);

      // Scroll to the bottom of the chat box
      chatBox.scrollTop = chatBox.scrollHeight;
    }
  </script>
</body>
</html>
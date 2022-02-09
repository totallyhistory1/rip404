var chat = {
  // (A) HELPER FUNCTION - SWAP BETWEEN SET NAME/SEND MESSAGE
  swapform : (direction) => {
    // (A1) SHOW SEND MESSAGE FORM
    if (direction) {
      document.getElementById("chat-name").style.display = "none";
      document.getElementById("chat-send").style.display = "grid";
    }

    // (A2) SHOW SET NAME FORM
    else {
      document.getElementById("chat-send").style.display = "none";
      document.getElementById("chat-name").style.display = "grid";
      document.getElementById("chat-name-go").disabled = false;
    }
  },

  // (B) START CHAT
  host : "ws://localhost:8080/", // @CHANGE to your own!
  name : "", // Current user name
  socket : null, // Websocket object
  htmltxt : null, // HTML send text field
  start : () => {
    // (B1) CREATE WEB SOCKET
    document.getElementById("chat-name-go").disabled = true;
    if (chat.htmltxt==null) { chat.htmltxt = document.getElementById("chat-send-text"); }
    chat.socket = new WebSocket(chat.host);

    // (B2) READY - CONNECTED TO SERVER
    chat.socket.onopen = (e) => {
      chat.name = document.getElementById("chat-name-set").value;
      chat.swapform(1);
    };

    // (B3) ON CONNECTION CLOSE
    chat.socket.onclose = (e) => { chat.swapform(0); };

    // (B4) ON RECEIVING DATA FROM SEREVER - UPDATE CHAT MESsAGES
    chat.socket.onmessage = (e) => {
      let msg = JSON.parse(e.data),
          row = document.createElement("div");
      row.innerHTML = `<div class="ch-name">${msg.n}</div><div class="ch-msg">${msg.m}</div>`;
      row.className = "ch-row";
      document.getElementById("chat-messages").appendChild(row);
    };

    // (B5) ON ERROR
    chat.socket.onerror = (e) => {
      chat.swapform(0);
      console.error(e);
      alert(`Failed to connect to ${chat.host}`);
    };

    return false;
  },

  // (C) SEND MESSAGE
  send : () => {
    let message = JSON.stringify({
      n: chat.name,
      m: chat.htmltxt.value
    });
    chat.htmltxt.value = "";
    chat.socket.send(message);
    return false;
  }
};

document.addEventListener('DOMContentLoaded', function() {
    // Voeg chat icon en widget toe aan de pagina
    const chatHtml = `
        <div class="chat-container">
            <div class="chat-icon">
                <i class="fas fa-comment"></i>
            </div>
            <div class="chat-widget">
                <div class="chat-header">
                    <div>Apothecare Assistent</div>
                    <div class="chat-close"><i class="fas fa-times"></i></div>
                </div>
                <div class="chat-messages">
                    <div class="chat-message bot-message">
                        Hallo! Hoe kan ik je helpen met vragen over medicijnen of gezondheid?
                    </div>
                </div>
                <div class="chat-input">
                    <textarea placeholder="Stel een vraag..."></textarea>
                    <button class="chat-send"><i class="fas fa-paper-plane"></i></button>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', chatHtml);
    
    // Elementen ophalen
    const chatIcon = document.querySelector('.chat-icon');
    const chatWidget = document.querySelector('.chat-widget');
    const chatClose = document.querySelector('.chat-close');
    const chatMessages = document.querySelector('.chat-messages');
    const chatInput = document.querySelector('.chat-input textarea');
    const chatSend = document.querySelector('.chat-send');
    
    // Chat openen en sluiten
    chatIcon.addEventListener('click', () => {
        chatWidget.classList.add('active');
    });
    
    chatClose.addEventListener('click', () => {
        chatWidget.classList.remove('active');
    });
    
    // Bericht verzenden functie
    function sendMessage() {
        const message = chatInput.value.trim();
        if (!message) return;
        
        // Toon gebruikersbericht
        const userMsgElement = document.createElement('div');
        userMsgElement.className = 'chat-message user-message';
        userMsgElement.textContent = message;
        chatMessages.appendChild(userMsgElement);
        
        // Scroll naar beneden
        chatMessages.scrollTop = chatMessages.scrollHeight;
        
        // Maak inputveld leeg
        chatInput.value = '';
        
        // Toon laad-indicator
        const loadingElement = document.createElement('div');
        loadingElement.className = 'chat-message bot-message';
        loadingElement.innerHTML = '<div class="loading-dots"><span></span><span></span><span></span></div>';
        chatMessages.appendChild(loadingElement);
        chatMessages.scrollTop = chatMessages.scrollHeight;
        
        // Controleer relatief pad naar API
        // Voor lokale testing, console debuggen
        console.log("Sending request to API...");
        
        // Verzend vraag naar API
        fetch('../api/chat.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                message: message
            })
        })
        .then(response => {
            console.log("API Response status:", response.status);
            return response.json();
        })
        .then(data => {
            console.log("API Response data:", data);
            
            // Verwijder laad-indicator
            chatMessages.removeChild(loadingElement);
            
            // Toon antwoord
            const botMsgElement = document.createElement('div');
            botMsgElement.className = 'chat-message bot-message';
            botMsgElement.textContent = data.success ? data.message : 'Sorry, er ging iets mis.';
            chatMessages.appendChild(botMsgElement);
            
            // Scroll naar beneden
            chatMessages.scrollTop = chatMessages.scrollHeight;
        })
        .catch(error => {
            console.error("API Error:", error);
            
            // Verwijder laad-indicator bij fout
            chatMessages.removeChild(loadingElement);
            
            // Toon foutmelding
            const errorElement = document.createElement('div');
            errorElement.className = 'chat-message bot-message';
            errorElement.textContent = 'Sorry, er ging iets mis bij het verwerken van je vraag.';
            chatMessages.appendChild(errorElement);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        });
    }
    
    // Event listeners voor verzenden
    chatSend.addEventListener('click', sendMessage);
    
    chatInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });
});
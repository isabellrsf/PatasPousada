// js/notificacoes.js
function mostrarNotificacao(msg, tipo='sucesso'){
    let div = document.getElementById('notificacao');
    
    // Se o elemento não existe, cria
    if(!div){
        div = document.createElement('div');
        div.id = 'notificacao';
        div.style.position = 'fixed';
        div.style.top = '10px';
        div.style.right = '10px';
        div.style.padding = '10px';
        div.style.borderRadius = '5px';
        div.style.color = 'white';
        div.style.zIndex = '1000';
        div.style.display = 'none';
        document.body.appendChild(div);
    }

    div.innerText = msg;

    // Define cor da notificação
    if(tipo === 'sucesso') div.style.background = '#28a745'; // verde
    else if(tipo === 'erro') div.style.background = '#dc3545'; // vermelho
    else if(tipo === 'info') div.style.background = '#007bff'; // azul

    div.style.display = 'block';

    setTimeout(() => { div.style.display = 'none'; }, 3000);
}

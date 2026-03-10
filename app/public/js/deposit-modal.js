let currentTransactionId = null;
let statusCheckInterval = null;

function openDepositModal() {
    const depositModal = document.getElementById('depositModal');
    const depositForm = document.getElementById('depositForm');
    const depositQRCode = document.getElementById('depositQRCode');
    const qrLogoFavicon = document.getElementById('qrLogoFavicon');
    
    if (depositModal) depositModal.classList.add('show');
    if (depositForm) depositForm.style.display = 'block';
    if (depositQRCode) depositQRCode.classList.add('deposit-qr-hidden');
    if (qrLogoFavicon) qrLogoFavicon.style.display = 'none';
    
    const depositAmount = document.getElementById('depositAmount');
    const depositDescription = document.getElementById('depositDescription');
    const pixCode = document.getElementById('pixCode');
    const qrCodeImage = document.getElementById('qrCodeImage');
    
    if (depositAmount) depositAmount.value = '0,00';
    if (depositDescription) depositDescription.value = '';
    if (pixCode) pixCode.value = '';
    if (qrCodeImage) qrCodeImage.src = '';
    
    // Resetar botão
    const button = document.getElementById('generatePixBtn');
    if (button) {
        button.disabled = false;
        button.innerHTML = '<svg width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" class="deposit-btn-icon"><path fill="currentColor" d="M5.25 4C4.56 4 4 4.56 4 5.25V8a1 1 0 0 1-2 0V5.25A3.25 3.25 0 0 1 5.25 2H8a1 1 0 0 1 0 2zm0 16C4.56 20 4 19.44 4 18.75V16a1 1 0 1 0-2 0v2.75A3.25 3.25 0 0 0 5.25 22H8a1 1 0 1 0 0-2zM20 5.25C20 4.56 19.44 4 18.75 4H16a1 1 0 1 1 0-2h2.75A3.25 3.25 0 0 1 22 5.25V8a1 1 0 1 1-2 0zM18.75 20c.69 0 1.25-.56 1.25-1.25V16a1 1 0 1 1 2 0v2.75A3.25 3.25 0 0 1 18.75 22H16a1 1 0 1 1 0-2zM7 7h3v3H7zm7 3h-4v4H7v3h3v-3h4v3h3v-3h-3zm0 0V7h3v3z"></path></svg> Gerar QR Code';
    }
    currentTransactionId = null;
    if (statusCheckInterval) {
        clearInterval(statusCheckInterval);
        statusCheckInterval = null;
    }
}

function closeDepositModal() {
    document.getElementById('depositModal').classList.remove('show');
    if (statusCheckInterval) {
        clearInterval(statusCheckInterval);
        statusCheckInterval = null;
    }
}

async function generateDepositPix(event) {
    if (event) {
        event.preventDefault();
    }
    
    const amountInput = document.getElementById('depositAmount');
    const amount = parseFloat(amountInput.value.replace(',', '.')) || 0;
    const button = document.getElementById('generatePixBtn') || document.querySelector('#depositForm .deposit-btn-primary');
    const originalText = button.innerHTML;
    
    if (!amount || amount < 1) {
        alert('Por favor, insira um valor válido (mínimo R$ 1,00)');
        return;
    }
    
    button.disabled = true;
    button.innerHTML = '<span class="loading-spinner"></span> Gerando...';
    
    try {
        const response = await fetch(window.depositCreateRoute, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ 
                amount: amount
                // Tempo de expiração fixo em 15 minutos (definido no backend)
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Usar external_id se disponível, caso contrário usar transaction_id
            currentTransactionId = data.external_id || data.transaction_id;
            
            // EXTRAIR CÓDIGO PIX (EMV) DA RESPOSTA
            let pixCode = null;
            let pixCodeSource = null;
            
            if (data.pix && data.pix.emv) {
                pixCode = data.pix.emv;
                pixCodeSource = 'pix.emv';
            } else if (data.pix && data.pix.payload) {
                pixCode = data.pix.payload;
                pixCodeSource = 'pix.payload';
            } else if (data.pix && data.pix.qrcode) {
                pixCode = data.pix.qrcode;
                pixCodeSource = 'pix.qrcode';
            } else if (data.pix && data.pix.code) {
                pixCode = data.pix.code;
                pixCodeSource = 'pix.code';
            } else if (data.pix_code) {
                pixCode = data.pix_code;
                pixCodeSource = 'pix_code';
            } else if (data.emv) {
                pixCode = data.emv;
                pixCodeSource = 'emv';
            }
            
            if (!pixCode) {
                console.error('DepositController: Código PIX não encontrado na resposta', data);
                alert('Erro: Código PIX não encontrado na resposta. Estrutura recebida: ' + JSON.stringify(Object.keys(data)) + '. Tente novamente ou entre em contato com o suporte.');
                button.disabled = false;
                button.innerHTML = originalText;
                return;
            }
            
            console.log('DepositController: Código PIX extraído com sucesso', {
                source: pixCodeSource,
                length: pixCode.length,
                preview: pixCode.substring(0, 50) + '...'
            });
            
            // Exibir código PIX (EMV) para copiar
            document.getElementById('pixCode').value = pixCode;
            
            // Atualizar valor exibido
            const displayAmount = parseFloat(amountInput.value.replace(',', '.')) || amount;
            document.getElementById('depositAmountDisplay').textContent = 'R$ ' + displayAmount.toFixed(2).replace('.', ',');
            
            // GERAR QR CODE NO FRONTEND usando qrcode.js
            const qrCodeCanvas = document.createElement('canvas');
            const qrCodeImage = document.getElementById('qrCodeImage');
            
            try {
                // Usar QRCode.js para gerar QR Code a partir do código EMV
                QRCode.toCanvas(qrCodeCanvas, pixCode, {
                    width: 160,
                    margin: 1,
                    color: {
                        dark: '#000000',
                        light: '#FFFFFF'
                    },
                    errorCorrectionLevel: 'M'
                }, function (error) {
                    if (error) {
                        console.error('Erro ao gerar QR Code:', error);
                        // Fallback: usar API pública se qrcode.js falhar
                        qrCodeImage.src = 'https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=' + encodeURIComponent(pixCode);
                        // Mostrar favicon mesmo com fallback
                        setTimeout(function() {
                            document.getElementById('qrLogoFavicon').style.display = 'block';
                        }, 100);
                    } else {
                        // Converter canvas para data URL
                        qrCodeImage.src = qrCodeCanvas.toDataURL('image/png');
                        // Mostrar favicon pequeno no centro do QR code
                        setTimeout(function() {
                            document.getElementById('qrLogoFavicon').style.display = 'block';
                        }, 100);
                    }
                });
            } catch (error) {
                console.error('Erro ao gerar QR Code:', error);
                // Fallback: usar API pública
                qrCodeImage.src = 'https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=' + encodeURIComponent(pixCode);
                // Mostrar favicon mesmo com fallback
                setTimeout(function() {
                    document.getElementById('qrLogoFavicon').style.display = 'block';
                }, 100);
            }
            
            // Se já temos qr_code_base64 do backend, usar (mas não é necessário)
            if (data.qr_code_base64 && !qrCodeImage.src) {
                qrCodeImage.src = 'data:image/svg+xml;base64,' + data.qr_code_base64;
                document.getElementById('qrLogoFavicon').style.display = 'block';
            } else if (data.pix && data.pix.qr_code_base64 && !qrCodeImage.src) {
                qrCodeImage.src = 'data:image/svg+xml;base64,' + data.pix.qr_code_base64;
                document.getElementById('qrLogoFavicon').style.display = 'block';
            }
            
            // Garantir que o favicon está visível após a imagem carregar
            qrCodeImage.onload = function() {
                setTimeout(function() {
                    const favicon = document.getElementById('qrLogoFavicon');
                    if (favicon && qrCodeImage.src) {
                        favicon.style.display = 'block';
                    }
                }, 200);
            };
            
            // Se a imagem já estiver carregada (cached), mostrar favicon
            if (qrCodeImage.complete && qrCodeImage.src) {
                setTimeout(function() {
                    document.getElementById('qrLogoFavicon').style.display = 'block';
                }, 200);
            }
            
            // Mostrar seção de QR Code e esconder formulário
            const depositForm = document.getElementById('depositForm');
            const depositQRCode = document.getElementById('depositQRCode');
            if (depositForm) depositForm.style.display = 'none';
            if (depositQRCode) depositQRCode.classList.remove('deposit-qr-hidden');
            
            // Resetar botão (caso precise gerar novo)
            button.disabled = false;
            
            // Iniciar verificação de status
            startStatusCheck();
        } else {
            alert('Erro ao gerar PIX: ' + (data.error || 'Erro desconhecido'));
            button.disabled = false;
            button.innerHTML = originalText;
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao gerar PIX. Tente novamente.');
        button.disabled = false;
        button.innerHTML = originalText;
    }
}

function setQuickAmount(value) {
    const input = document.getElementById('depositAmount');
    const currentValue = parseFloat(input.value.replace(',', '.')) || 0;
    const newValue = currentValue + value;
    input.value = newValue.toFixed(2).replace('.', ',');
}

function copyPixCode() {
    const pixCodeInput = document.getElementById('pixCode');
    const pixCode = pixCodeInput.value;
    
    if (navigator.clipboard) {
        navigator.clipboard.writeText(pixCode).then(() => {
            const originalValue = pixCodeInput.value;
            pixCodeInput.value = 'Código copiado!';
            pixCodeInput.style.color = '#10b981';
            setTimeout(() => {
                pixCodeInput.value = originalValue;
                pixCodeInput.style.color = '#ffffff';
            }, 2000);
        });
    } else {
        // Fallback para navegadores mais antigos
        const textarea = document.createElement('textarea');
        textarea.value = pixCode;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        
        const originalValue = pixCodeInput.value;
        pixCodeInput.value = 'Código copiado!';
        pixCodeInput.style.color = '#10b981';
        setTimeout(() => {
            pixCodeInput.value = originalValue;
            pixCodeInput.style.color = '#ffffff';
        }, 2000);
    }
}

async function checkDepositStatus(showMessage = true) {
    if (!currentTransactionId) {
        if (showMessage) {
            alert('Nenhuma transação encontrada. Gere um novo PIX primeiro.');
        }
        return;
    }
    
    const checkBtn = document.getElementById('checkDepositStatusBtn');
    const originalBtnText = checkBtn ? checkBtn.innerHTML : '';
    
    // Mostrar loading no botão
    if (checkBtn && showMessage) {
        checkBtn.disabled = true;
        checkBtn.innerHTML = '<span class="loading-spinner"></span> Verificando...';
    }
    
    try {
        const response = await fetch(window.depositStatusRoute.replace(':id', currentTransactionId));
        const data = await response.json();
        
        if (data.success) {
            if (data.status === 'paid') {
                // Pagamento confirmado!
                if (statusCheckInterval) {
                    clearInterval(statusCheckInterval);
                    statusCheckInterval = null;
                }
                
                if (showMessage) {
                    // Mostrar mensagem de sucesso
                    if (checkBtn) {
                        checkBtn.innerHTML = '✅ Pagamento confirmado!';
                        checkBtn.style.background = '#10b981';
                        checkBtn.style.color = '#ffffff';
                    }
                    
                    // Fechar modal e recarregar página após 1.5 segundos
                    setTimeout(() => {
                        closeDepositModal();
                        // Recarregar a página para atualizar o dashboard
                        window.location.reload();
                    }, 1500);
                } else {
                    // Se foi chamado automaticamente, apenas atualizar
                    if (statusCheckInterval) {
                        clearInterval(statusCheckInterval);
                        statusCheckInterval = null;
                    }
                    // Recarregar página após 2 segundos
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                }
            } else if (data.status === 'expired') {
                // PIX expirado
                if (statusCheckInterval) {
                    clearInterval(statusCheckInterval);
                    statusCheckInterval = null;
                }
                
                if (showMessage) {
                    alert('PIX expirado. Gere um novo PIX.');
                    if (checkBtn) {
                        checkBtn.disabled = false;
                        checkBtn.innerHTML = originalBtnText;
                    }
                }
            } else {
                // Ainda pendente
                if (showMessage) {
                    alert('Pagamento ainda não confirmado. Aguarde alguns instantes e tente novamente.');
                    if (checkBtn) {
                        checkBtn.disabled = false;
                        checkBtn.innerHTML = originalBtnText;
                    }
                }
            }
        } else {
            if (showMessage) {
                alert('Erro ao verificar status: ' + (data.error || 'Erro desconhecido'));
                if (checkBtn) {
                    checkBtn.disabled = false;
                    checkBtn.innerHTML = originalBtnText;
                }
            }
        }
    } catch (error) {
        console.error('Erro ao verificar status:', error);
        if (showMessage) {
            alert('Erro ao verificar status do pagamento. Tente novamente.');
            if (checkBtn) {
                checkBtn.disabled = false;
                checkBtn.innerHTML = originalBtnText;
            }
        }
    }
}

function startStatusCheck() {
    // Verificar status a cada 5 segundos (sem mostrar mensagens)
    statusCheckInterval = setInterval(() => checkDepositStatus(false), 5000);
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    // Fechar modal quando clicar no backdrop
    const depositModalBackdrop = document.getElementById('depositModalBackdrop');
    if (depositModalBackdrop) {
        depositModalBackdrop.addEventListener('click', closeDepositModal);
    }
    
    // Fechar modal quando clicar no botão de fechar
    const closeDepositModalBtn = document.getElementById('closeDepositModalBtn');
    if (closeDepositModalBtn) {
        closeDepositModalBtn.addEventListener('click', closeDepositModal);
    }
    
    // Formulário de depósito
    const depositFormElement = document.getElementById('depositFormElement');
    if (depositFormElement) {
        depositFormElement.addEventListener('submit', function(event) {
            event.preventDefault();
            generateDepositPix(event);
        });
    }
    
    // Botões de quantidade rápida
    const quickAmountButtons = document.querySelectorAll('.deposit-quick-btn[data-amount]');
    quickAmountButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const amount = parseFloat(this.getAttribute('data-amount'));
            setQuickAmount(amount);
        });
    });
    
    // Botão de copiar código PIX
    const copyPixCodeBtn = document.getElementById('copyPixCodeBtn');
    if (copyPixCodeBtn) {
        copyPixCodeBtn.addEventListener('click', copyPixCode);
    }
    
    // Botão de verificar status do depósito
    const checkDepositStatusBtn = document.getElementById('checkDepositStatusBtn');
    if (checkDepositStatusBtn) {
        checkDepositStatusBtn.addEventListener('click', checkDepositStatus);
    }
    
    // Tratamento de erro do favicon (fallback para .ico se .svg falhar)
    const qrLogoFavicon = document.getElementById('qrLogoFavicon');
    if (qrLogoFavicon) {
        qrLogoFavicon.addEventListener('error', function() {
            const currentSrc = this.src;
            if (currentSrc && currentSrc.endsWith('.svg')) {
                this.src = currentSrc.replace('.svg', '.ico');
            }
        });
    }
    
    // Botão para abrir modal de depósito
    const openDepositModalBtn = document.getElementById('openDepositModalBtn');
    if (openDepositModalBtn) {
        openDepositModalBtn.addEventListener('click', openDepositModal);
    }
});


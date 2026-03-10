/**
 * Onboarding JavaScript
 * Funcionalidades para o formulário de onboarding
 */

(function() {
    'use strict';

    /**
     * Update document field based on account type
     */
    function updateDocumentField() {
        // Use jQuery if available, otherwise use vanilla JS
        if (typeof jQuery !== 'undefined') {
            const accountType = jQuery('input[name="account_type"]:checked').val();
            const documentLabel = jQuery('#document-label');
            const documentField = jQuery('#onb-document');
            const pessoaJuridicaFields = jQuery('#pessoa-juridica-fields');
            const businessType = jQuery('#onb-business-type');
            const businessSector = jQuery('#onb-business-sector');

            if (!accountType || documentLabel.length === 0 || documentField.length === 0) return;

            if (accountType === 'pessoa_fisica') {
                documentLabel.text('CPF');
                documentField.attr('placeholder', '000.000.000-00');
                
                if (jQuery.fn.mask) {
                    documentField.unmask().mask('000.000.000-00');
                }
                
                pessoaJuridicaFields.hide();
                businessType.prop('required', false);
                businessSector.prop('required', false);
            } else {
                documentLabel.text('CNPJ');
                documentField.attr('placeholder', '00.000.000/0000-00');
                
                if (jQuery.fn.mask) {
                    documentField.unmask().mask('00.000.000/0000-00');
                }
                
                pessoaJuridicaFields.show();
                businessType.prop('required', true);
                businessSector.prop('required', true);
            }
        } else {
            // Fallback to vanilla JS
            const accountType = document.querySelector('input[name="account_type"]:checked');
            if (!accountType) return;

            const documentLabel = document.getElementById('document-label');
            const documentField = document.getElementById('onb-document');
            const pessoaJuridicaFields = document.getElementById('pessoa-juridica-fields');
            const businessType = document.getElementById('onb-business-type');
            const businessSector = document.getElementById('onb-business-sector');

            if (!documentLabel || !documentField) return;

            if (accountType.value === 'pessoa_fisica') {
                documentLabel.textContent = 'CPF';
                documentField.setAttribute('placeholder', '000.000.000-00');
                
                if (pessoaJuridicaFields) {
                    pessoaJuridicaFields.style.display = 'none';
                }
                
                if (businessType) {
                    businessType.removeAttribute('required');
                }
                
                if (businessSector) {
                    businessSector.removeAttribute('required');
                }
            } else {
                documentLabel.textContent = 'CNPJ';
                documentField.setAttribute('placeholder', '00.000.000/0000-00');
                
                if (pessoaJuridicaFields) {
                    pessoaJuridicaFields.style.display = 'block';
                }
                
                if (businessType) {
                    businessType.setAttribute('required', 'required');
                }
                
                if (businessSector) {
                    businessSector.setAttribute('required', 'required');
                }
            }
        }
    }

    /**
     * Auto-detect account type based on document length
     */
    function autoDetectAccountType() {
        if (typeof jQuery !== 'undefined') {
            const documentField = jQuery('#onb-document');
            const documentValue = documentField.val();
            
            // Se já tem documento preenchido, detectar o tipo pela quantidade de caracteres
            if (documentValue) {
                const cleanDoc = documentValue.replace(/\D/g, '');
                if (cleanDoc.length === 14) {
                    // CNPJ - pessoa jurídica
                    jQuery('#document-label').text('CNPJ');
                    documentField.attr('placeholder', '00.000.000/0000-00');
                    if (jQuery.fn.mask) {
                        documentField.unmask().mask('00.000.000/0000-00');
                    }
                    jQuery('input[name="account_type"][value="pessoa_juridica"]').prop('checked', true);
                    updateDocumentField();
                } else if (cleanDoc.length === 11) {
                    // CPF - pessoa física
                    jQuery('#document-label').text('CPF');
                    documentField.attr('placeholder', '000.000.000-00');
                    if (jQuery.fn.mask) {
                        documentField.unmask().mask('000.000.000-00');
                    }
                    jQuery('input[name="account_type"][value="pessoa_fisica"]').prop('checked', true);
                    updateDocumentField();
                }
            }
            
            documentField.on('input', function() {
                const value = jQuery(this).val().replace(/\D/g, '');
                
                if (value.length === 11) {
                    // CPF detected
                    jQuery('input[name="account_type"][value="pessoa_fisica"]').prop('checked', true);
                    updateDocumentField();
                } else if (value.length === 14) {
                    // CNPJ detected
                    jQuery('input[name="account_type"][value="pessoa_juridica"]').prop('checked', true);
                    updateDocumentField();
                }
            });
        } else {
            const documentField = document.getElementById('onb-document');
            if (!documentField) return;

            documentField.addEventListener('input', function() {
                const value = this.value.replace(/\D/g, '');
                
                if (value.length === 11) {
                    // CPF detected
                    const pessoaFisicaRadio = document.querySelector('input[name="account_type"][value="pessoa_fisica"]');
                    if (pessoaFisicaRadio) {
                        pessoaFisicaRadio.checked = true;
                        updateDocumentField();
                    }
                } else if (value.length === 14) {
                    // CNPJ detected
                    const pessoaJuridicaRadio = document.querySelector('input[name="account_type"][value="pessoa_juridica"]');
                    if (pessoaJuridicaRadio) {
                        pessoaJuridicaRadio.checked = true;
                        updateDocumentField();
                    }
                }
            });
        }
    }

    /**
     * Initialize CEP lookup
     */
    function initCepLookup() {
        if (typeof jQuery !== 'undefined') {
            jQuery('#onb-cep').on('blur', function() {
                const cep = jQuery(this).val().replace(/\D/g, '');
                
                if (cep.length !== 8) return;

                jQuery.getJSON(`https://viacep.com.br/ws/${cep}/json/`, function(data) {
                    if (!data.erro) {
                        jQuery('#onb-rua').val(data.logradouro || '');
                        jQuery('#onb-bairro').val(data.bairro || '');
                        jQuery('#onb-cidade').val(data.localidade || '');
                        jQuery('#onb-estado').val(data.uf || '');
                    }
                });
            });
        } else {
            const cepField = document.getElementById('onb-cep');
            if (!cepField) return;

            cepField.addEventListener('blur', function() {
                const cep = this.value.replace(/\D/g, '');
                
                if (cep.length !== 8) return;

                // Fetch CEP data from ViaCEP API
                fetch(`https://viacep.com.br/ws/${cep}/json/`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.erro) {
                            console.error('CEP não encontrado');
                            return;
                        }

                        const ruaField = document.getElementById('onb-rua');
                        const bairroField = document.getElementById('onb-bairro');
                        const cidadeField = document.getElementById('onb-cidade');
                        const estadoField = document.getElementById('onb-estado');

                        if (ruaField) ruaField.value = data.logradouro || '';
                        if (bairroField) bairroField.value = data.bairro || '';
                        if (cidadeField) cidadeField.value = data.localidade || '';
                        if (estadoField) estadoField.value = data.uf || '';
                    })
                    .catch(error => {
                        console.error('Erro ao buscar CEP:', error);
                    });
            });
        }
    }

    /**
     * Initialize onboarding page
     */
    function initOnboarding() {
        // Wait for jQuery if it's being used
        if (typeof jQuery !== 'undefined') {
            jQuery(document).ready(function($) {
                // Apply masks
                const cepField = $('#onb-cep');
                if (cepField.length && $.fn.mask) {
                    cepField.mask('00000-000');
                }

                // Update document field on page load
                updateDocumentField();

                // Update document field when account type changes
                $('input[name="account_type"]').on('change', function() {
                    updateDocumentField();
                });

                // Auto-detect account type
                autoDetectAccountType();

                // Initialize CEP lookup
                initCepLookup();
            });
        } else {
            // Fallback to vanilla JS
            function init() {
                // Update document field on page load
                updateDocumentField();

                // Update document field when account type changes
                const accountTypeRadios = document.querySelectorAll('input[name="account_type"]');
                accountTypeRadios.forEach(function(radio) {
                    radio.addEventListener('change', updateDocumentField);
                });

                // Auto-detect account type
                autoDetectAccountType();

                // Initialize CEP lookup
                initCepLookup();
            }

            // Initialize when DOM is ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init);
            } else {
                init();
            }
        }
    }

    // Initialize when DOM is ready
    if (typeof jQuery !== 'undefined') {
        // jQuery will handle initialization
        initOnboarding();
    } else {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initOnboarding);
        } else {
            initOnboarding();
        }
    }

    // Export function for global access
    window.updateDocumentField = updateDocumentField;
})();


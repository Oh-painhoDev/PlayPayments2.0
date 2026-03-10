// Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Variáveis globais
    let chart;
    const salesDataRaw = window.dashboardSalesData?.data || [];
    const labels = window.dashboardSalesData?.labels || [];
    const startDate = window.dashboardStartDate || '';
    const endDate = window.dashboardEndDate || '';
    const dashboardRoute = window.dashboardRoute || '/dashboard';
    
    // Converte os valores para números (caso venham como strings)
    const salesData = salesDataRaw.map(val => parseFloat(val) || 0);
    
    // Configuração do gráfico ApexCharts
    const chartOptions = {
        series: [{
            name: 'Vendas',
            data: salesData
        }],
        chart: {
            type: 'area',
            height: 350,
            toolbar: {
                show: false
            },
            zoom: {
                enabled: false
            },
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 800
            },
            background: 'transparent'
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth',
            width: 3,
            colors: ['#00C853']
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.7,
                opacityTo: 0.3,
                stops: [0, 90, 100],
                colorStops: [
                    {
                        offset: 0,
                        color: '#00C853',
                        opacity: 0.7
                    },
                    {
                        offset: 100,
                        color: '#00C853',
                        opacity: 0.1
                    }
                ]
            }
        },
        colors: ['#00C853'],
        xaxis: {
            categories: labels,
            labels: {
                style: {
                    colors: '#9CA3AF',
                    fontSize: '12px'
                }
            },
            axisBorder: {
                show: false
            },
            axisTicks: {
                show: false
            }
        },
        yaxis: {
            labels: {
                style: {
                    colors: '#9CA3AF',
                    fontSize: '12px'
                },
                formatter: function(value) {
                    return 'R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
                }
            }
        },
        grid: {
            borderColor: '#2C2C2E',
            strokeDashArray: 4,
            xaxis: {
                lines: {
                    show: false
                }
            },
            yaxis: {
                lines: {
                    show: true
                }
            },
            padding: {
                top: 0,
                right: 0,
                bottom: 0,
                left: 0
            }
        },
        tooltip: {
            theme: 'dark',
            style: {
                fontSize: '12px'
            },
            y: {
                formatter: function(value) {
                    return 'R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }
            }
        },
        theme: {
            mode: 'dark'
        }
    };
    
    // Inicializa o gráfico
    if (typeof ApexCharts !== 'undefined' && document.getElementById('tradingviewChart')) {
        chart = new ApexCharts(document.querySelector('#tradingviewChart'), chartOptions);
        chart.render();
    }
        
    // Controles de timeframe
    document.querySelectorAll('.timeframe-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.timeframe-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const timeframe = this.getAttribute('data-timeframe');
            
            // Aqui você pode adicionar lógica para atualizar o gráfico com diferentes períodos
            // Por enquanto, apenas atualiza o botão ativo
        });
    });

    // Date Filter Modal
    const dateFilterBtn = document.getElementById('dateFilterBtn');
    const dateFilterModal = document.getElementById('dateFilterModal');
    const closeModalBtn = document.querySelector('.close-modal-btn');
    const cancelBtn = document.querySelector('.cancel-btn');
    const applyBtn = document.querySelector('.apply-btn');
    const clearFilterBtn = document.querySelector('.clear-filter-btn');
    const dateOptions = document.querySelectorAll('.date-option');
    const customDateRange = document.getElementById('customDateRange');
    const selectedDateRange = document.getElementById('selectedDateRange');
    const dateRangeText = document.getElementById('dateRangeText');
    const refreshBtn = document.getElementById('refreshBtn');

    if (dateFilterBtn && dateFilterModal && typeof flatpickr !== 'undefined') {
        // Inicializar Flatpickr (calendário)
        const fp = flatpickr(customDateRange, {
            mode: "range",
            dateFormat: "d M, Y",
            locale: "pt",
            defaultDate: startDate && endDate ? [startDate, endDate] : [],
            disableMobile: true,
            onChange: function(selectedDates, dateStr) {
                if (selectedDates.length === 2 && selectedDateRange) {
                    selectedDateRange.textContent = dateStr;
                }
            }
        });

        // Abrir modal
        dateFilterBtn.addEventListener('click', function() {
            dateFilterModal.classList.add('active');
        });

        // Fechar modal
        function closeModal() {
            dateFilterModal.classList.remove('active');
        }

        if (closeModalBtn) closeModalBtn.addEventListener('click', closeModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

        // Opções de data rápida
        dateOptions.forEach(option => {
            option.addEventListener('click', function() {
                const days = parseInt(this.dataset.days);
                const endDateObj = new Date();
                let startDateObj;

                if (days === 0) {
                    // Hoje
                    startDateObj = new Date();
                } else if (days === 1) {
                    // Ontem
                    startDateObj = new Date();
                    startDateObj.setDate(startDateObj.getDate() - 1);
                    endDateObj.setDate(endDateObj.getDate() - 1);
                } else {
                    // Outros períodos
                    startDateObj = new Date();
                    startDateObj.setDate(startDateObj.getDate() - (days - 1));
                }

                fp.setDate([startDateObj, endDateObj]);
                
                // Destacar opção selecionada
                dateOptions.forEach(opt => opt.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Aplicar filtro
        if (applyBtn) {
            applyBtn.addEventListener('click', function() {
                const dates = fp.selectedDates;
                if (dates.length === 2) {
                    const startDateFormatted = formatDate(dates[0]);
                    const endDateFormatted = formatDate(dates[1]);
                    
                    // Atualizar texto do botão de filtro
                    if (dateRangeText) {
                        const formattedStart = dates[0].toLocaleDateString('pt-BR', {day: '2-digit', month: 'short', year: 'numeric'}).replace('.', '');
                        const formattedEnd = dates[1].toLocaleDateString('pt-BR', {day: '2-digit', month: 'short', year: 'numeric'}).replace('.', '');
                        dateRangeText.textContent = `${formattedStart} - ${formattedEnd}`;
                    }
                    
                    // Redirecionar para a mesma página com os parâmetros de data
                    window.location.href = `${dashboardRoute}?date_from=${startDateFormatted}&date_to=${endDateFormatted}`;
                }
                closeModal();
            });
        }

        // Limpar filtro
        if (clearFilterBtn) {
            clearFilterBtn.addEventListener('click', function() {
                window.location.href = dashboardRoute;
            });
        }
    }

    // Atualizar página
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            location.reload();
        });
    }

    // Função auxiliar para formatar data
    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    // Modal de Infrações - Abrir quando clicar no alerta
    const blockedBalanceAlert = document.querySelector('.blocked-balance-alert');
    if (blockedBalanceAlert) {
        blockedBalanceAlert.addEventListener('click', function() {
            const disputesModal = document.getElementById('disputesModal');
            if (disputesModal) {
                disputesModal.classList.add('show');
            }
        });
    }
});

// Modal de Infrações - Fechar
function closeDisputesModal() {
    const disputesModal = document.getElementById('disputesModal');
    if (disputesModal) {
        disputesModal.classList.remove('show');
    }
}

// Fechar modal quando clicar no backdrop
document.addEventListener('DOMContentLoaded', function() {
    const disputesModal = document.getElementById('disputesModal');
    if (disputesModal) {
        const backdrop = disputesModal.querySelector('.disputes-modal-backdrop');
        if (backdrop) {
            backdrop.addEventListener('click', closeDisputesModal);
        }
    }
});





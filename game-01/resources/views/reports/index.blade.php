<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generador de Reportes Crediticios</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8" x-data="reportGenerator()">
        <div class="max-w-2xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-800 mb-8">Generador de Reportes Crediticios</h1>
            
            <!-- Formulario de filtros -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Filtros del Reporte</h2>
                
                <form @submit.prevent="generateReport()" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Inicio</label>
                            <input type="date" x-model="filters.start_date" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Fin</label>
                            <input type="date" x-model="filters.end_date" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    
                    <div class="flex justify-center">
                        <button type="submit" :disabled="loading"
                                class="px-8 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors disabled:opacity-50 font-medium">
                            <span x-show="!loading">Generar Reporte Excel</span>
                            <span x-show="loading" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Generando...
                            </span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Estado del trabajo -->
            <div x-show="jobStatus" class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-lg font-semibold mb-4">Estado del Reporte</h3>
                
                <div x-show="jobStatus?.status === 'processing' || jobStatus?.status === 'queued'" class="flex items-center space-x-3">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                    <span>Procesando reporte grande en segundo plano...</span>
                    <span class="text-sm text-gray-500" x-text="'Registros estimados: ' + (jobStatus?.estimated_records || 0)"></span>
                </div>
                
                <div x-show="jobStatus?.status === 'completed'" class="space-y-3">
                    <div class="flex items-center space-x-3 text-green-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>Reporte completado exitosamente</span>
                    </div>
                    <button @click="downloadReport()" 
                            class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
                        Descargar Reporte Excel
                    </button>
                </div>

                <div x-show="jobStatus?.status === 'failed'" class="space-y-3">
                    <div class="flex items-center space-x-3 text-red-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        <span>Error al generar el reporte</span>
                    </div>
                </div>
            </div>

            <!-- Mensajes -->
            <div x-show="message" class="mb-6">
                <div :class="messageType === 'error' ? 'bg-red-100 border-red-400 text-red-700' : 'bg-green-100 border-green-400 text-green-700'"
                     class="border px-4 py-3 rounded">
                    <span x-text="message"></span>
                </div>
            </div>
        </div>
    </div>

    <script>
        function reportGenerator() {
            return {
                filters: {
                    start_date: '2025-12-01',
                    end_date: '2025-12-31'
                },
                loading: false,
                message: '',
                messageType: 'success',
                jobStatus: null,
                jobId: null,
                statusInterval: null,

                async generateReport() {
                    this.loading = true;
                    this.message = '';
                    this.jobStatus = null;
                    
                    try {
                        const formData = new FormData();
                        formData.append('start_date', this.filters.start_date);
                        formData.append('end_date', this.filters.end_date);
                        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

                        const response = await fetch('/reports/export', {
                            method: 'POST',
                            body: formData
                        });

                        if (response.headers.get('content-type')?.includes('application/json')) {
                            // Reporte grande - procesamiento en background
                            const data = await response.json();
                            this.jobId = data.job_id;
                            this.jobStatus = {
                                status: 'queued',
                                estimated_records: data.estimated_records
                            };
                            this.showMessage(data.message, 'success');
                            this.startStatusCheck();
                        } else {
                            // Reporte pequeño - descarga directa
                            const blob = await response.blob();
                            const url = window.URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = url;
                            a.download = `reporte_crediticio_${new Date().toISOString().slice(0,10)}.xlsx`;
                            a.click();
                            window.URL.revokeObjectURL(url);
                            this.showMessage('Reporte descargado exitosamente', 'success');
                        }
                    } catch (error) {
                        this.showMessage('Error al generar el reporte', 'error');
                    } finally {
                        this.loading = false;
                    }
                },

                startStatusCheck() {
                    this.statusInterval = setInterval(async () => {
                        try {
                            const response = await fetch(`/reports/status/${this.jobId}`);
                            const data = await response.json();
                            this.jobStatus = data;
                            
                            if (data.status === 'completed' || data.status === 'failed') {
                                clearInterval(this.statusInterval);
                            }
                        } catch (error) {
                            clearInterval(this.statusInterval);
                        }
                    }, 3000);
                },

                async downloadReport() {
                    window.location.href = `/reports/download/${this.jobId}`;
                },

                showMessage(text, type = 'success') {
                    this.message = text;
                    this.messageType = type;
                    setTimeout(() => {
                        this.message = '';
                    }, 5000);
                }
            }
        }
    </script>
</body>
</html>
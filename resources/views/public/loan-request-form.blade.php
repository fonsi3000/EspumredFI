<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Préstamo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('{{ asset("images/fondo.png") }}');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            font-family: 'Helvetica', 'Arial', sans-serif;
        }
        
        .form-container {
            width: 100%;
            max-width: 750px;
            transform: scale(0.98);
        }
        
        .card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            background-color: white;
            overflow: hidden;
        }
        
        .card-header {
            background-color: #FF6B24;
            color: white;
            padding: 1.2rem 1.5rem;
            text-align: center;
            border: none;
        }
        
        .card-header h3 {
            font-size: 1.8rem;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .section-title {
            font-weight: bold;
            font-size: 1.1rem;
            margin-bottom: 1rem;
            color: #212529;
        }
        
        /* Input styles */
        .form-control {
            border-radius: 50px;
            padding: 0.5rem 1rem;
            height: 46px;
            border: 1px solid #ced4da;
            font-size: 0.95rem;
        }
        
        .form-control::placeholder {
            color: #6c757d;
            font-size: 0.95rem;
        }
        
        .form-select {
            border-radius: 50px;
            padding: 0.5rem 1rem;
            height: 46px;
            border: 1px solid #ced4da;
            font-size: 0.95rem;
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 16px 12px;
        }
        
        /* Amount input group */
        .amount-input-group {
            display: flex;
            align-items: center;
            border: 1px solid #ced4da;
            border-radius: 50px;
            overflow: hidden;
            background-color: white;
            height: 46px;
        }
        
        .amount-input-group .currency-symbol {
            padding: 0 0 0 1rem;
            font-weight: normal;
            color: #212529;
            background-color: transparent;
            border: none;
            font-size: 1rem;
        }
        
        .amount-input-group input {
            flex-grow: 1;
            border: none;
            border-radius: 0;
            padding-left: 0.3rem;
            height: 44px;
            background-color: transparent;
        }
        
        .amount-input-group input:focus {
            box-shadow: none;
            outline: none;
        }
        
        /* Rows and spacing */
        .form-row {
            margin-bottom: 0.75rem;
        }
        
        .form-section {
            margin-bottom: 0.75rem;
        }
        
        /* Dividers */
        .divider {
            height: 1px;
            background-color: #e9ecef;
            margin: 1rem 0;
            width: 100%;
        }
        
        /* File upload */
        .file-upload-container {
            margin-bottom: 0.5rem;
        }
        
        .file-upload-label {
            display: block;
            margin-bottom: 0.25rem;
            font-size: 0.95rem;
        }
        
        .file-input-wrapper {
            position: relative;
            width: 100%;
        }
        
        .custom-file-input {
            border: 1px solid #ced4da;
            border-radius: 50px;
            width: 100%;
            padding: 0.5rem 1rem;
            background-color: white;
            display: flex;
            align-items: center;
            height: 46px;
            cursor: pointer;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .file-input-text {
            color: #6c757d;
            font-size: 0.95rem;
            margin-left: 0.5rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .file-input {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }
        
        .file-upload-help {
            font-size: 0.8rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }
        
        /* Submit button */
        .submit-button {
            background-color: #FF6B24;
            border: none;
            border-radius: 50px;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            padding: 0.75rem;
            width: 100%;
            font-size: 1rem;
            margin-top: 0.5rem;
            letter-spacing: 0.5px;
            transition: background-color 0.2s;
        }
        
        .submit-button:hover {
            background-color: #e45e1f;
        }
        
        /* Footer logos */
        .footer-logos {
            text-align: center;
            margin-top: 1rem;
        }
        
        .footer-logos img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="card">
            <div class="card-header">
                <h3>Solicitud de Préstamo</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('public.loan-request.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    @if ($errors->any())
                        <div class="alert alert-danger mb-3">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <!-- Información del solicitante -->
                    <div class="form-section">
                        <h5 class="section-title">Información del solicitante</h5>
                        <div class="row form-row">
                            <div class="col-md-4">
                                <input type="text" class="form-control" id="name" name="name" placeholder="Nombre completo" value="{{ old('name') }}" required>
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control" id="document_number" name="document_number" placeholder="Documento" value="{{ old('document_number') }}" required>
                            </div>
                            <div class="col-md-4">
                                <input type="tel" class="form-control" id="phone" name="phone" placeholder="Teléfono" value="{{ old('phone') }}" required>
                            </div>
                        </div>
                        <div class="row form-row">
                            <div class="col-md-4">
                                <input type="email" class="form-control" id="email" name="email" placeholder="Correo Electrónico" value="{{ old('email') }}" required>
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control" id="area" name="area" placeholder="Proceso o Área" value="{{ old('area') }}" required>
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control" id="position" name="position" placeholder="Cargo" value="{{ old('position') }}" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="divider"></div>
                    
                    <!-- Detalles del préstamo -->
                    <div class="form-section">
                        <h5 class="section-title">Detalles del préstamo</h5>
                        <div class="row form-row">
                            <div class="col-md-6">
                                <div class="amount-input-group">
                                    <span class="currency-symbol">$</span>
                                    <input type="number" class="form-control" id="amount" name="amount" placeholder="Monto solicitado" value="{{ old('amount') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <input type="number" class="form-control" id="term_months" name="term_months" placeholder="Plazo (meses)" value="{{ old('term_months') }}" required>
                            </div>
                        </div>
                        <div class="row form-row">
                            <div class="col-md-12">
                                <select class="form-select" id="loan_reason" name="loan_reason" required>
                                    <option value="" selected disabled>Motivo del préstamo</option>
                                    @foreach($loanReasons as $value => $label)
                                        <option value="{{ $value }}" {{ old('loan_reason') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="divider"></div>
                    
                    <!-- Documentación -->
                    <div class="form-section">
                        <h5 class="section-title">Documentación</h5>
                        <div class="file-upload-container">
                            <label class="file-upload-label">Documento de garantía</label>
                            <div class="file-input-wrapper">
                                <div class="custom-file-input">
                                    <i class="bi bi-file-earmark"></i>
                                    <span id="file-name" class="file-input-text">No se ha seleccionado ningún archivo</span>
                                </div>
                                <input type="file" class="file-input" id="guarantee_document" name="guarantee_document" accept="application/pdf">
                            </div>
                            <div class="file-upload-help">Subir documento en formato PDF. Máximo 5MB.</div>
                        </div>
                    </div>
                    
                    <button type="submit" class="submit-button">Enviar Solicitud</button>
                </form>
            </div>
        </div>
        
        <div class="footer-logos">
            <img src="{{ asset('images/logos.png') }}" alt="Logos corporativos">
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Manejar el cambio en el campo de archivo para mostrar el nombre del archivo seleccionado
            document.getElementById('guarantee_document').addEventListener('change', function() {
                var fileName = this.files[0] ? this.files[0].name : 'No se ha seleccionado ningún archivo';
                document.getElementById('file-name').textContent = fileName;
            });
        });
    </script>
</body>
</html>
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

        .form-control,
        .form-select {
            border-radius: 50px;
            padding: 0.5rem 1rem;
            height: 46px;
            border: 1px solid #ced4da;
            font-size: 0.95rem;
        }

        .form-control::placeholder {
            color: #6c757d;
        }

        textarea.form-control {
            border-radius: 20px;
            height: auto;
            padding: 0.75rem 1rem;
        }

        .form-row {
            margin-bottom: 0.75rem;
        }

        .form-section {
            margin-bottom: 0.75rem;
        }

        .divider {
            height: 1px;
            background-color: #e9ecef;
            margin: 1rem 0;
            width: 100%;
        }

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
        }

        .file-input-text {
            color: #6c757d;
            font-size: 0.95rem;
            margin-left: 0.5rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
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
        }

        .submit-button:hover {
            background-color: #e45e1f;
        }

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
                <form action="{{ route('public.loan-request-litoral.store') }}" method="POST" enctype="multipart/form-data">
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
                            <div class="col-12 col-sm-6 col-md-4 mb-2">
                                <input type="text" class="form-control" name="name" placeholder="Nombre completo" value="{{ old('name') }}" required>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4 mb-2">
                                <input type="text" class="form-control" name="document_number" placeholder="Documento" value="{{ old('document_number') }}" required>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4 mb-2">
                                <input type="tel" class="form-control" name="phone" placeholder="Teléfono" value="{{ old('phone') }}" required>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4 mb-2">
                                <input type="email" class="form-control" name="email" placeholder="Correo Electrónico" value="{{ old('email') }}" required>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4 mb-2">
                                <input type="text" class="form-control" name="area" placeholder="Proceso o Área" value="{{ old('area') }}" required>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4 mb-2">
                                <input type="text" class="form-control" name="position" placeholder="Cargo" value="{{ old('position') }}" required>
                            </div>
                            <div class="col-12 mb-2">
                                <select class="form-select" name="company" required>
                                    <option value="" selected disabled>Selecciona tu empresa.</option>
                                    <option value="espumas_medellin" {{ old('company') == 'espumas_medellin' ? 'selected' : '' }}>Espumas medellin S.A</option>
                                    <option value="espumados_litoral" {{ old('company') == 'espumados_litoral' ? 'selected' : '' }}>Espumados del litoral S.A</option>
                                    <option value="ctn_carga" {{ old('company') == 'ctn_carga' ? 'selected' : '' }}>STN Carga y logistica</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="divider"></div>

                    <!-- Detalles del préstamo -->
                    <div class="form-section">
                        <h5 class="section-title">Detalles del préstamo</h5>
                        <div class="row form-row">
                            <div class="col-12">
                                <select class="form-select" name="loan_reason" required>
                                    <option value="" selected disabled>Motivo del préstamo</option>
                                    @foreach($loanReasons as $value => $label)
                                        <option value="{{ $value }}" {{ old('loan_reason') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row form-row mt-3">
                            <div class="col-12">
                                <textarea class="form-control" name="description" placeholder="Descripción del préstamo" rows="3">{{ old('description') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="divider"></div>

                    <!-- Documentación -->
                    <div class="form-section">
                        <h5 class="section-title">Documentación</h5>
                        <div class="file-upload-container">
                            <label class="file-upload-label">Documento</label>
                            <div class="file-input-wrapper">
                                <div class="custom-file-input">
                                    <span class="file-input-text" id="file-name">No se ha seleccionado ningún archivo</span>
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
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('guarantee_document');
            const fileNameDisplay = document.getElementById('file-name');

            input.addEventListener('change', function () {
                fileNameDisplay.textContent = this.files.length ? this.files[0].name : 'No se ha seleccionado ningún archivo';
            });
        });
    </script>
</body>
</html>
# SP Treinamentos LMS

Plataforma LMS baseada em WordPress + Tutor LMS, com regras de estudo, certificados e emissão de carteirinha de estudante. Este repositório inclui configurações de servidor (Nginx) e um plugin WordPress com funcionalidades customizadas.

## Visao Geral
- **Core do LMS**: plugin `sp-lms-custom-core` com regras de tempo de estudo, certificados e carteirinha.
- **Infraestrutura**: guia de setup para Ubuntu 22.04, Nginx, PHP 8.1, MariaDB e Redis.
- **Multisite-ready**: criação automática das tabelas em ambientes multisite.

## Estrutura do Repositorio
- `sp-treinamentos-lms/server-conf/nginx.conf`: exemplo de configuracao Nginx.
- `sp-treinamentos-lms/server-conf/setup-guide.md`: passo a passo completo de setup.
- `sp-treinamentos-lms/wp-content/plugins/sp-lms-custom-core/`: plugin WordPress custom.

## Funcionalidades do Plugin
### 1) Controle de Tempo de Estudo (Watchtime)
- Registra minutos assistidos por usuario/dia.
- Limite diario padrao: **8 horas** (configurado em `class-watchtime-tracker.php`).
- Bloqueia conclusao de aulas quando o limite diario e atingido.
- Usuarios marcados como `special` nao sofrem limite diario.

### 2) Regras de Certificado
- Garante tempo minimo entre matricula e conclusao do curso.
- Calcula dias minimos com base na carga horaria: `total_horas / 8`.
- Permite logica especifica para usuarios `special`.

### 3) Emissao de Carteirinha
- Endpoint: `/carteirinha/COURSE_ID`.
- Permite emissao apenas para cursos concluidos.
- Gera PDF usando FPDF (dependencia externa).

### 4) Gerenciamento de Banco
Cria as tabelas automaticamente na ativacao do plugin:
- `wp_user_watchtime`
- `wp_student_type`
- `wp_course_certificate_model`

## Requisitos
- **Servidor**: Ubuntu 22.04 LTS
- **Stack**: Nginx, PHP 8.1, MariaDB, Redis
- **WordPress** e **Tutor LMS**
- **FPDF** para emissao de PDF da carteirinha

## Instalacao (Resumo)
1) Configure o servidor seguindo `sp-treinamentos-lms/server-conf/setup-guide.md`.
2) Instale o WordPress.
3) Copie o plugin para `wp-content/plugins/sp-lms-custom-core/`.
4) Ative o plugin no painel do WordPress.
5) Garanta que o Tutor LMS esteja instalado e ativo.

## Configuracoes Importantes
- **Limite diario de estudo**: `SP_LMS_Watchtime_Tracker::DAILY_LIMIT_SECONDS`.
- **Usuarios especiais**: tabela `wp_student_type` com valor `special`.
- **Carga horaria do curso**: meta `sp_course_total_hours` no curso (usada para calcular dias minimos).

## Endpoints
- **Carteirinha**: `/carteirinha/COURSE_ID`
  - Exige login
  - Exige curso concluido
  - Requer FPDF instalado

## Multisite
O plugin cria as tabelas automaticamente para todos os sites quando ativado em modo rede.

## Notas de Desenvolvimento
- Arquivo principal: `sp-treinamentos-lms/wp-content/plugins/sp-lms-custom-core/sp-lms-custom-core.php`
- Classes:
  - `class-db-manager.php`
  - `class-watchtime-tracker.php`
  - `class-certificate-rules.php`
  - `class-id-card-generator.php`

## Roadmap (Sugestoes)
- Adicionar UI administrativa para definir carga horaria e regras de certificado.
- Integrar geracao real de PDF/QR Code (com biblioteca e layout final).
- Relatorios de estudo por aluno/curso.

## Licenca
A definir.

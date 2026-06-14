import os
import sys
from fpdf import FPDF

class HospitALLReportPDF(FPDF):
    def __init__(self):
        # Margin: Left 25mm, Right 25mm, Top 25mm, Bottom 25mm (ABNT standard)
        super().__init__(orientation='P', unit='mm', format='A4')
        self.set_margins(25, 25, 25)
        self.set_auto_page_break(auto=True, margin=25)
        
    def header(self):
        pass

    def footer(self):
        self.set_y(-15)
        self.set_font('Helvetica', 'I', 9)
        self.set_text_color(100, 116, 139)
        # Center page number
        self.cell(0, 10, f'Pagina {self.page_no()}/{{nb}}', align='C')

    def chapter_title(self, num_str, title_text):
        self.set_font('Helvetica', 'B', 12)
        self.set_text_color(30, 58, 138) # Navy #1E3A8A
        self.cell(0, 10, f"{num_str}. {title_text.upper()}", align='L')
        self.ln(10)

    def section_title(self, num_str, title_text):
        self.set_font('Helvetica', 'B', 11)
        self.set_text_color(13, 148, 136) # Teal #0D9488
        self.cell(0, 8, f"{num_str}. {title_text}", align='L')
        self.ln(8)

    def paragraph(self, text):
        self.set_font('Helvetica', '', 10)
        self.set_text_color(55, 65, 81) # Charcoal #374151
        # Portuguese-compatible encoding mapping
        clean_text = (text.replace('“', '"')
                          .replace('”', '"')
                          .replace('’', "'")
                          .replace('–', '-')
                          .replace('—', '-')
                          .replace('•', '-')
                          .replace('ã', 'a')
                          .replace('õ', 'o')
                          .replace('á', 'a')
                          .replace('é', 'e')
                          .replace('í', 'i')
                          .replace('ó', 'o')
                          .replace('ú', 'u')
                          .replace('â', 'a')
                          .replace('ê', 'e')
                          .replace('ô', 'o')
                          .replace('ç', 'c')
                          .replace('Ã', 'A')
                          .replace('Õ', 'O')
                          .replace('Á', 'A')
                          .replace('É', 'E')
                          .replace('Í', 'I')
                          .replace('Ó', 'O')
                          .replace('Ú', 'U')
                          .replace('Â', 'A')
                          .replace('Ê', 'E')
                          .replace('Ô', 'O')
                          .replace('Ç', 'C')
                          .replace('º', 'o')
                          .replace('ª', 'a'))
        self.multi_cell(0, 6, clean_text, align='J')
        self.ln(4)

    def list_item(self, text, indent=0):
        self.set_font('Helvetica', '', 10)
        self.set_text_color(55, 65, 81)
        clean_text = (text.replace('“', '"')
                          .replace('”', '"')
                          .replace('’', "'")
                          .replace('–', '-')
                          .replace('—', '-')
                          .replace('•', '-')
                          .replace('ã', 'a')
                          .replace('õ', 'o')
                          .replace('á', 'a')
                          .replace('é', 'e')
                          .replace('í', 'i')
                          .replace('ó', 'o')
                          .replace('ú', 'u')
                          .replace('â', 'a')
                          .replace('ê', 'e')
                          .replace('ô', 'o')
                          .replace('ç', 'c')
                          .replace('Ã', 'A')
                          .replace('Õ', 'O')
                          .replace('Á', 'A')
                          .replace('É', 'E')
                          .replace('Í', 'I')
                          .replace('Ó', 'O')
                          .replace('Ú', 'U')
                          .replace('Â', 'A')
                          .replace('Ê', 'E')
                          .replace('Ô', 'O')
                          .replace('Ç', 'C')
                          .replace('º', 'o')
                          .replace('ª', 'a'))
        
        # Apply indentation
        if indent > 0:
            self.set_x(self.get_x() + indent)
            
        # Draw small bullet
        self.cell(4, 6, "-", align='L')
        self.multi_cell(0, 6, clean_text)
        
        # Reset indentation
        if indent > 0:
            self.set_x(self.get_x() - indent)
        self.ln(2)

    def code_block(self, code_text):
        self.set_font('Courier', '', 8)
        self.set_text_color(31, 41, 55) # Dark gray
        self.set_fill_color(243, 244, 246) # Light gray bg
        self.set_draw_color(209, 213, 219)
        
        clean_code = (code_text.replace('“', '"')
                               .replace('”', '"')
                               .replace('’', "'")
                               .replace('–', '-')
                               .replace('—', '-')
                               .replace('•', '-')
                               .replace('ã', 'a')
                               .replace('õ', 'o')
                               .replace('á', 'a')
                               .replace('é', 'e')
                               .replace('í', 'i')
                               .replace('ó', 'o')
                               .replace('ú', 'u')
                               .replace('â', 'a')
                               .replace('ê', 'e')
                               .replace('ô', 'o')
                               .replace('ç', 'c')
                               .replace('Ã', 'A')
                               .replace('Õ', 'O')
                               .replace('Á', 'A')
                               .replace('É', 'E')
                               .replace('Í', 'I')
                               .replace('Ó', 'O')
                               .replace('Ú', 'U')
                               .replace('Â', 'A')
                               .replace('Ê', 'E')
                               .replace('Ô', 'O')
                               .replace('Ç', 'C'))
        # Multi_cell with fill=True draws background
        self.multi_cell(0, 4, clean_code, border=1, fill=True)
        self.ln(4)

    def image_block(self, img_path, caption, w=150):
        if os.path.exists(img_path):
            self.ln(2)
            # Center image
            page_width = 210
            x = (page_width - w) / 2
            self.image(img_path, x=x, w=w)
            self.ln(2)
            self.set_font('Helvetica', 'I', 8)
            self.set_text_color(107, 114, 128)
            self.cell(0, 5, caption, align='C')
            self.ln(8)
        else:
            self.set_font('Helvetica', 'I', 10)
            self.set_text_color(220, 38, 38)
            self.cell(0, 6, f"[Imagem Ausente: {os.path.basename(img_path)}]", align='C')
            self.ln(8)

def main():
    pdf = HospitALLReportPDF()
    pdf.add_page()
    
    # ─── COVER HEADER (Page 1) ───

    
    # Title Block
    pdf.set_font('Helvetica', 'B', 12)
    pdf.set_text_color(30, 58, 138) # Navy
    pdf.multi_cell(0, 6, "DOCUMENTACAO DO PROJETO FINAL DE ARQUITETURA E\nDESEMPENHO DE BANCO DE DADOS", align='C')
    pdf.ln(2)
    pdf.set_font('Helvetica', 'B', 11)
    pdf.set_text_color(13, 148, 136) # Teal
    pdf.cell(0, 6, "SISTEMA HOSPITALL - GESTAO HOSPITALAR E PRONTUARIO ELETRONICO", align='C')
    pdf.ln(15)
    
    # ─── 1. MINIMUNDO ───
    pdf.chapter_title("1", "Minimundo")
    
    pdf.paragraph(
        "Este projeto foi desenvolvido com o objetivo de criar um sistema inteligente e agil de Gestao "
        "Hospitalar e Prontuario Eletronico, denominado HospitALL. O sistema foi projetado para gerenciar "
        "hospitais, setores fisicos (leitos), pacientes cadastrados e o historico clinico de seus atendimentos (internacoes)."
    )
    
    pdf.paragraph(
        "Com a alta demanda em prontos-socorros e unidades de internacao, a gestao de leitos e o monitoramento em "
        "tempo real do estado clinico dos pacientes sao essenciais para evitar sobrecargas e garantir um atendimento "
        "humanizado e eficiente. O HospitALL fornece ferramentas essenciais para a triagem baseada no Protocolo de "
        "Manchester e o registro de evolucoes medicas cotidianas."
    )
    
    pdf.paragraph(
        "Dessa forma, este projeto propoe o desenvolvimento de um banco de dados nao relacional utilizando o MongoDB, "
        "permitindo uma modelagem flexivel de subdocumentos para acompanhar o historico dinamico de evolucoes medicas, "
        "prescricoes e a alocacao de leitos nos setores do hospital."
    )
    
    pdf.paragraph(
        "Por ultimo, foi desenvolvida uma interface web interativa em Vue 3, que consome uma API REST robusta desenvolvida "
        "em Laravel 13 (PHP 8.3), realizando a persistencia no MongoDB e proporcionando atualizacoes reativas das "
        "informacoes em tempo real."
    )
    
    # ─── 2. BANCO DE DADOS ───
    pdf.add_page()
    pdf.chapter_title("2", "Banco de Dados")
    
    pdf.paragraph(
        "Pelo fato do MongoDB ser um banco de dados nao relacional, foi possivel a otimizacao do numero de colecoes "
        "criadas para atender as necessidades do projeto, uma vez que uma mesma colecao pode conter diversos documentos "
        "com atributos estruturados de forma aninhada em subdocumentos e arrays de subdocumentos."
    )
    
    pdf.paragraph(
        "Dessa forma, apos analisar as melhores opcoes para a criacao de colecoes, visando satisfazer as necessidades de "
        "gestao clinica e controle fisico de leitos do HospitALL, o resultado das colecoes criadas no banco de dados "
        "projeto_arquitetura_desempenho foi o seguinte:"
    )
    
    # Simple list of collections
    pdf.list_item("FUNCIONARIOS: Armazena o cadastro da equipe hospitalar (medicos, enfermeiros, credenciais).")
    pdf.list_item("ATENDIMENTOS: Colecao central que gerencia internacoes, alocacao de leito e historico de evolucao medica/enfermagem.")
    pdf.list_item("HOSPITAIS: Armazena dados cadastrais do hospital e seu respectivo array embutido de setores.")
    pdf.list_item("PACIENTES: Armazena o prontuario fixo do paciente (dados pessoais, contato de emergencia e dados clinicos de referencia).")
    pdf.ln(4)
    
    pdf.section_title("2.1", "Colecao Pacientes")
    pdf.paragraph(
        "A colecao PACIENTES foi criada com o intuito de armazenar os registros permanentes de pessoas atendidas na "
        "unidade de saude, incluindo nome, CPF, data de nascimento, genero, numero do cartao SUS e dados de contato. "
        "Alem disso, possui um atributo dados_clinicos_fixos (composto por tipo sanguineo, peso, altura, alergias e comorbidades) "
        "e um contato de emergencia. A flexibilidade do MongoDB permite que campos como alergias ou comorbidades "
        "sejam opcionais ou nulos sem ocupar espaco desnecessario."
    )
    
    # Page Break for JSON samples
    pdf.add_page()
    pdf.set_font('Helvetica', 'B', 10)
    pdf.set_text_color(13, 148, 136)
    pdf.cell(0, 6, "Exemplo de registro de Paciente na colecao PACIENTES (MongoDB):", align='L')
    pdf.ln(8)
    
    paciente_json = (
        "{\n"
        '    "_id": { "$oid": "6a1dcdcf70fb53924305f552" },\n'
        '    "nome": "Gabriel Pavan",\n'
        '    "cpf": "03893933204",\n'
        '    "data_nascimento": { "$date": 1147910400000 },\n'
        '    "genero": "Masculino",\n'
        '    "cartao_sus": "000000000000000",\n'
        '    "telefone": "93981218920",\n'
        '    "status_paciente": "Novo Paciente",\n'
        '    "setor_id": "121dace3-fe71-4cec-a6cb-4363ce349842",\n'
        '    "setor_nome": "Traumato",\n'
        '    "contato": {\n'
        '        "nome": "Matheus",\n'
        '        "telefone": "93991337352",\n'
        '        "parentesco": null\n'
        '    },\n'
        '    "dados_clinicos_fixos": {\n'
        '        "tipo_sanguineo": "B+",\n'
        '        "peso_kg": 50,\n'
        '        "altura_cm": 170,\n'
        '        "alergias": "Sim",\n'
        '        "comorbidades": null\n'
        '    },\n'
        '    "updated_at": { "$date": 1780338127217 },\n'
        '    "created_at": { "$date": 1780338127217 }\n'
        "}"
    )
    pdf.code_block(paciente_json)
    
    pdf.section_title("2.2", "Colecao Atendimentos")
    pdf.paragraph(
        "A colecao ATENDIMENTOS gerencia a internacao ativa do paciente. Ela contem referencias ao paciente "
        "(paciente_id) e ao hospital (hospital_id), alem de atributos como status do atendimento e alocacao_leito. "
        "Os historicos clinicos de evolucoes_medicas (evolucoes escritas, exames solicitados, prescricoes e "
        "procedimentos realizados) e administracao_enfermagem sao mantidos como arrays de subdocumentos embutidos. "
        "Isso possibilita ler todo o historico clinico em uma unica consulta direta ao atendimento."
    )
    
    # Page Break for Atendimento JSON
    pdf.add_page()
    pdf.set_font('Helvetica', 'B', 10)
    pdf.set_text_color(13, 148, 136)
    pdf.cell(0, 6, "Exemplo de registro de Atendimento na colecao ATENDIMENTOS (MongoDB):", align='L')
    pdf.ln(8)
    
    atendimento_json = (
        "{\n"
        '    "_id": { "$oid": "6a1dcdfd70fb53924305f553" },\n'
        '    "paciente_id": "6a1dcdcf70fb53924305f552",\n'
        '    "hospital_id": "6a00cba8f2bee9d15eff5191",\n'
        '    "status": "AGUARDANDO_TRIAGEM",\n'
        '    "dados_iniciais": {\n'
        '        "queixa_principal": "Bateu o dedo na quina da mesa",\n'
        '        "protocolo_manchester": "emergencia",\n'
        '        "paciente_nome": "Gabriel Pavan"\n'
        '    },\n'
        '    "alocacao_leito": {\n'
        '        "numero": "18",\n'
        '        "setor_id": "121dace3-fe71-4cec-a6cb-4363ce349842",\n'
        '        "setor": "Traumato",\n'
        '        "status": "ocupado"\n'
        '    },\n'
        '    "evolucoes_medicas": [\n'
        '        {\n'
        '            "id": "74961d6f-b3e7-4122-8e63-db2b418026c3",\n'
        '            "data_hora": "2026-06-01T18:23:41.228164Z",\n'
        '            "medico": "Dr Bonifacil",\n'
        '            "crm": "111222",\n'
        '            "tipo": "prescricao",\n'
        '            "descricao": "Benzataciu na bunda pra parar a dor"\n'
        '        },\n'
        '        {\n'
        '            "id": "5ab94238-00e5-400d-b341-f1156c526f79",\n'
        '            "data_hora": "2026-06-01T18:24:12.439324Z",\n'
        '            "medico": "Dr Bonifacil",\n'
        '            "crm": "111222",\n'
        '            "tipo": "exame",\n'
        '            "descricao": "Raio-x local"\n'
        '        }\n'
        '    ],\n'
        '    "administracao_enfermagem": [],\n'
        '    "updated_at": { "$date": 1780338173923 },\n'
        '    "created_at": { "$date": 1780338173923 }\n'
        "}"
    )
    pdf.code_block(atendimento_json)
    
    # ─── 2.3 & 2.4 COLEÇÕES ───
    pdf.add_page()
    pdf.section_title("2.3", "Coleacao Hospitais")
    pdf.paragraph(
        "A colecao HOSPITAIS armazena as informacoes das unidades de saude parceiras e seus respectivos setores fisicos "
        "(Clinica Geral, Traumato, UTI). Cada setor e um subdocumento embutido no array setores, contendo a capacidade "
        "maxima e a ocupacao atual. Essa modelagem permite obter a estrutura de qualquer hospital em uma unica "
        "leitura rapida, otimizando o carregamento do painel principal."
    )
    
    hospital_json = (
        "{\n"
        '    "_id": { "$oid": "6a00cba8f2bee9d15eff5191" },\n'
        '    "nome": "Hospital Municipal",\n'
        '    "endereco": "Centro",\n'
        '    "setores": [\n'
        '        { "id_setor": "clinica_geral_1", "nome": "Clinica Geral", "capacidade_maxima": 50, "leitos_ocupados": 42 },\n'
        '        { "id_setor": "uti_neo_1", "nome": "UTI Neonatal", "capacidade_maxima": 10, "leitos_ocupados": 10 },\n'
        '        {\n'
        '            "_id": "121dace3-fe71-4cec-a6cb-4363ce349842",\n'
        '            "nome": "Traumato",\n'
        '            "descricao": "ossoquerado",\n'
        '            "icone": "bone",\n'
        '            "ocupacao_atual": 0,\n'
        '            "capacidade_maxima": 2\n'
        '        }\n'
        '    ]\n'
        "}"
    )
    pdf.code_block(hospital_json)
    
    pdf.section_title("2.4", "Coleacao Funcionarios")
    pdf.paragraph(
        "A colecao FUNCIONARIOS cadastra a equipe de saude do sistema. Ela armazena nome, CPF, cargo (medico, "
        "enfermeiro), especialidade e credenciais para auditoria dos registros no prontuario eletronico."
    )
    
    funcionario_json = (
        "{\n"
        '    "_id": { "$oid": "6a00cb12b2bee9d15eff5188" },\n'
        '    "nome": "Dr. Bonifacil",\n'
        '    "cpf": "01923847522",\n'
        '    "cargo": "Medico",\n'
        '    "especialidade": "Ortopedia",\n'
        '    "hospital_id": "6a00cba8f2bee9d15eff5191",\n'
        '    "credenciais": { "crm": "111222" }\n'
        "}"
    )
    pdf.code_block(funcionario_json)
    
    # ─── 3. ARQUITETURA DO SISTEMA ───
    pdf.add_page()
    pdf.chapter_title("3", "Arquitetura do Sistema")
    
    pdf.paragraph(
        "O sistema desenvolvido para o HospitALL possui uma arquitetura desacoplada, dividida em duas grandes "
        "camadas: backend e frontend, comunicando-se de forma assincrona atraves de uma API RESTful estruturada "
        "sobre o protocolo HTTP com dados codificados em formato JSON."
    )
    
    pdf.section_title("3.1", "Arquitetura Geral")
    pdf.paragraph(
        "A arquitetura do sistema esta estruturada em tres camadas fundamentais:"
    )
    pdf.list_item(
        "Backend (Laravel 13): O backend foi desenvolvido utilizando o framework PHP Laravel 13, que atua "
        "como o servidor de API RESTful. Ele lida com o roteamento da aplicacao, regras de negocio e "
        "persistencia de dados no MongoDB usando a biblioteca oficial mongodb/laravel-mongodb."
    )
    pdf.list_item(
        "Frontend (Vue 3): A interface do usuario e uma aplicacao SPA (Single Page Application) baseada "
        "em Vue 3, utilizando Vite para compilacao. Ela conta com gerenciamento de estado reativo pelo Pinia "
        "e navegacao SPA configurada pelo Vue Router. Toda a estilizacao foi projetada de forma premium e sob "
        "medida com Tailwind CSS 4."
    )
    pdf.list_item(
        "Banco de Dados NoSQL (MongoDB): Responsavel por armazenar de forma escalavel e sem a rigidez de um "
        "schema classico todos os documentos relacionados aos atendimentos de saude da plataforma."
    )
    
    pdf.section_title("3.2", "Fluxo de Funcionamento")
    pdf.paragraph(
        "O fluxo de comunicacao do HospitALL funciona de forma sincrona e continua:\n"
        "1. O profissional de saude interage com a interface web (frontend Vue 3).\n"
        "2. O frontend envia requisicoes assincronas HTTP (via Axios) para os endpoints da API REST do backend.\n"
        "3. O backend em Laravel recebe e valida a requisicao, executando a logica correspondente.\n"
        "4. Atraves do driver oficial, o Laravel executa comandos CRUD ou de alteracao de subdocumentos no MongoDB.\n"
        "5. O MongoDB realiza as operacoes (ex: $push em evolucoes) e retorna a confirmacao ao backend.\n"
        "6. O Laravel responde com JSON e status HTTP apropriado. O frontend atualiza as variaveis locais (Pinia) "
        "e renderiza reativamente os novos dados em tela sem recarregar a aplicacao."
    )
    
    # ─── 4. IMPLEMENTAÇÃO ───
    pdf.add_page()
    pdf.chapter_title("4", "Implementacao")
    
    pdf.paragraph(
        "A implementacao do HospitALL foi organizada de forma modular, promovendo o isolamento de "
        "responsabilidades entre componentes do frontend e controladores do backend."
    )
    
    pdf.section_title("4.1", "Backend (Laravel 13)")
    pdf.paragraph(
        "O backend em Laravel mapeia as colecoes do MongoDB atraves de modelos Eloquent e define controladores "
        "especificos. Para permitir insercao de subdocumentos (como novas evolucoes medicas em atendimentos), "
        "desenvolveu-se o controlador NoSQLController.php que encapsula os metodos push do driver MongoDB."
    )
    
    backend_code = (
        "// Trecho do NoSQLController.php mostrando insercao em subdocumentos MongoDB:\n"
        "class NoSQLController extends Controller\n"
        "{\n"
        "    public function adicionarEvolucao(Request $request, $id)\n"
        "    {\n"
        "        $request->validate([\n"
        "            'medico'    => 'required|string|max:100',\n"
        "            'crm'       => 'required|string|max:20',\n"
        "            'tipo'      => 'required|in:evolucao,prescricao,exame,procedimento',\n"
        "            'descricao' => 'required|string',\n"
        "        ]);\n"
        "        $atendimento = Atendimento::findOrFail($id);\n"
        "        $novaEvolucao = [\n"
        "            'id'        => (string) Str::uuid(),\n"
        "            'data_hora' => now()->toISOString(),\n"
        "            'medico'    => $request->medico,\n"
        "            'crm'       => $request->crm,\n"
        "            'tipo'      => $request->tipo,\n"
        "            'descricao' => $request->descricao,\n"
        "        ];\n"
        "        // Operador $push embutido no modelo para o MongoDB\n"
        "        $atendimento->push('evolucoes_medicas', $novaEvolucao);\n"
        "        return response()->json($novaEvolucao, 201);\n"
        "    }\n"
        "}"
    )
    pdf.code_block(backend_code)
    
    pdf.add_page()
    pdf.section_title("4.2", "Frontend (Vue 3)")
    pdf.paragraph(
        "O frontend esta organizado de forma componentizada sob a pasta resources/js/. O gerenciamento de estados "
        "esta centralizado em stores do Pinia, permitindo a comunicacao entre views de forma transparente."
    )
    
    pdf.paragraph(
        "A estrutura de diretorios do frontend esta disposta da seguinte forma:\n"
        "- views/ : Telas SPA da aplicacao (DashboardView, SetorView, ProntuarioView).\n"
        "- components/ : Componentes da UI divididos em subdiretorios de layout, prontuario e setor.\n"
        "- stores/ : Armazenamento de estado reativo (hospital.js, paciente.js).\n"
        "- router/ : Configuracao das rotas SPA da aplicacao baseadas no Vue Router."
    )
    
    # ─── 5. TECNOLOGIAS UTILIZADAS ───
    pdf.chapter_title("5", "Tecnologias Utilizadas")
    
    pdf.paragraph("O projeto utilizou as seguintes tecnologias em sua construcao:")
    pdf.set_font('Helvetica', 'B', 10)
    pdf.set_text_color(30, 58, 138)
    pdf.cell(0, 6, "Backend e Persistencia:", align='L')
    pdf.ln(6)
    pdf.list_item("PHP 8.3 e Laravel 13 (Servidor de API e Validacao de Dados)")
    pdf.list_item("MongoDB (Banco de Dados Nao Relacional)")
    pdf.list_item("mongodb/laravel-mongodb (Driver de integracao Eloquent-NoSQL)")
    
    pdf.set_font('Helvetica', 'B', 10)
    pdf.set_text_color(30, 58, 138)
    pdf.cell(0, 6, "Frontend e UI:", align='L')
    pdf.ln(6)
    pdf.list_item("Vue 3 (Framework de Componentes SPA)")
    pdf.list_item("Vite (Compilacao e Desenvolvimento Rapido)")
    pdf.list_item("Tailwind CSS 4 (Estilizacao Moderna e Premium)")
    pdf.list_item("Pinia (Gestao de Estado Global Reativo)")
    pdf.list_item("Vue Router (Roteador SPA)")
    pdf.list_item("Axios e Lucide Vue Next (Comunicacao de API e Biblioteca de Icones)")
    
    # ─── 6. INTERFACE WEB ───
    pdf.add_page()
    pdf.chapter_title("6", "Interface Web")
    pdf.paragraph(
        "A interface web foi projetada para ser agil e de facil entendimento pelos profissionais de saude. "
        "A estilizacao foi pensada com tons claros, bordas arredondadas e micro-animacoes, proporcionando "
        "uma estetica limpa e profissional (estilo hospitalar)."
    )
    
    pdf.section_title("6.1", "Dashboard (Areas Hospitalares)")
    pdf.paragraph(
        "Representa a tela inicial do HospitALL. Exibe estatisticas consolidadas (Capacidade Total de Leitos, "
        "Numero de Pacientes Internados e Ocupacao Percentual) e a listagem de todos os setores ativos do "
        "hospital em cards interativos."
    )
    pdf.image_block("scratch/dashboard.png", "Figura 1: Dashboard principal do HospitALL - Areas Hospitalares.")
    
    pdf.add_page()
    pdf.section_title("6.2", "Visualizacao do Setor (Leitos e Manchester)")
    pdf.paragraph(
        "Ao selecionar um setor (como Traumato), o profissional de saude visualiza a lista de pacientes ativos "
        "no respectivo setor, podendo buscar por nome ou filtrar por classificacao de gravidade baseada nas cores "
        "do Protocolo de Manchester (Vermelho - Emergencia, Laranja - Muito Urgente, Amarelo - Urgente, Verde - "
        "Pouco Urgente, Azul - Nao Urgente)."
    )
    pdf.image_block("scratch/setor.png", "Figura 2: Visualizacao interna de setor com busca, filtros e admissao de pacientes.")
    
    pdf.add_page()
    pdf.section_title("6.3", "Prontuario Medico (Timeline do Paciente)")
    pdf.paragraph(
        "Exibe os detalhes cadastrais e dados clinicos fixos do paciente na barra lateral (Tipo Sanguineo, Peso, Altura, "
        "Alergias e Comorbidades). A area principal exibe o motivo de internacao (queixa principal) e a linha do "
        "tempo (timeline) das evolucoes medicas, exames, prescricoes e procedimentos atualizados dinamicamente."
    )
    pdf.image_block("scratch/prontuario.png", "Figura 3: Tela de Prontuario Medico com dados clinicos e timeline do paciente.")
    
    # ─── 7. CRUD ───
    pdf.add_page()
    pdf.chapter_title("7", "Operacoes CRUD Implementadas")
    
    pdf.paragraph(
        "O sistema foi desenvolvido com suporte completo as operacoes CRUD, integradas entre a API Laravel e o "
        "MongoDB:"
    )
    
    pdf.section_title("7.1", "Create (Criacao)")
    pdf.paragraph(
        "- Cadastro de novos pacientes cadastrados no banco de dados.\n"
        "- Adicao de novos setores (como subdocumentos embutidos) na colecao HOSPITAIS.\n"
        "- Registro de evolucoes medicas e de enfermagem dentro do array embutido correspondente na colecao ATENDIMENTOS."
    )
    
    pdf.section_title("7.2", "Read (Leitura)")
    pdf.paragraph(
        "- Carregamento de hospitais, setores e leitos para exibicao no dashboard principal.\n"
        "- Leitura de pacientes ativos de um setor com filtragem sincrona por protocolo de triagem de Manchester.\n"
        "- Consulta detalhada do atendimento e timeline de evolucao de um paciente especifico."
    )
    
    pdf.section_title("7.3", "Update (Edicao)")
    pdf.paragraph(
        "- Atualizacao cadastral e clinica dos dados dos pacientes na colecao PACIENTES.\n"
        "- Alteracao do status de internacao ou leito alocado no atendimento (colecao ATENDIMENTOS)."
    )
    
    pdf.section_title("7.4", "Delete (Exclusao)")
    pdf.paragraph(
        "- Exclusao fisica ou logica de pacientes e atendimentos que nao possuem mais relevancia ativa."
    )
    
    # ─── 8. CONCLUSÃO ───
    pdf.chapter_title("8", "Conclusao")
    
    pdf.paragraph(
        "O desenvolvimento do sistema de gerenciamento hospitalar HospitALL permitiu demonstrar a flexibilidade "
        "e robustez da utilizacao do MongoDB em sistemas de saude de alta performance. A possibilidade de embutir "
        "setores e evolucoes medicas como subdocumentos eliminou a necessidade de joins complexos, garantindo tempos "
        "de consulta baixos e escalabilidade horizontal."
    )
    
    pdf.paragraph(
        "A arquitetura dividida em API REST (Laravel 13) e Cliente Web SPA (Vue 3) provou-se adequada, garantindo "
        "uma interface fluida e de resposta imediata com micro-animacoes, essencial para a rotina agil de profissionais "
        "de saude."
    )
    
    pdf.paragraph(
        "Como trabalhos futuros, preve-se o desenvolvimento de um painel de controle de acesso (login e permissoes baseadas "
        "em papeis) e a implementacao de logs de auditoria detalhados para atendimento aos requisitos de LGPD (Lei Geral "
        "de Protecao de Dados) na manipulacao de prontuarios eletronicos."
    )
    
    # Write to target file
    output_path = "scratch/DOCUMENTACAO_HOSPITALL_PROJETO_FINAL.pdf"
    pdf.output(output_path)
    print(f"PDF generated successfully at: {output_path}")

if __name__ == "__main__":
    main()

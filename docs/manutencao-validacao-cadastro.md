# Solicitação de manutenção — Validação de dados cadastrais

**Disciplina:** Desenvolvimento de Sistemas
**Sistema:** HelpDesk TI — sistema de chamados de TI (CRUD em PHP/Laravel 13)
**Aluno:** Antonio Lira
**Atividade:** Manutenção corretiva e evolutiva de formulários
**Tecnologias:** HTML, CSS (Bootstrap), JavaScript, PHP e Laravel

---

## 1. Formulários escolhidos

A manutenção foi aplicada aos **dois formulários principais do HelpDesk TI**:

| Formulário | Telas | Quem usa | Itens da atividade aplicados |
|---|---|---|---|
| **Cadastro de usuários** | `/usuarios/create`, `/usuarios/{id}/edit` e `/cadastro` ("Cadastre-se") | Administrador e qualquer pessoa | 1.1 Nome · 1.2 E-mail · 1.3 Idade · **2.1 Data de nascimento** · **2.2 CPF** · 2.3 CEP · 2.4 Celular |
| **Registro de incidente** | `/chamados/create` e `/chamados/{id}/edit` | Quem abre e quem atende chamados | 1.1 Texto obrigatório (descrição resumida e descrição) · **3.1 Data inicial e data final** (período do incidente) |

**Por que esses itens:** CPF e data de nascimento, que são obrigatórios na atividade, identificam as **pessoas** do sistema. Por isso ficaram no cadastro de usuários. No **incidente**, que é o coração do HelpDesk, o período faz sentido de verdade: quando o problema começou e até quando ele deve ser resolvido (o "prazo", como no ServiceNow).

Os itens 1.4 (valor monetário), 3.2 (estoque) e 3.4 (desconto) não foram aplicados porque o sistema não tem compras, estoque ou vendas. Eles exigiriam módulos novos.

## 2. Situação ANTES da manutenção

### Cadastro de usuários

| Campo | Como estava | Problema |
|---|---|---|
| Nome | Obrigatório, até 255 caracteres | Aceitava 1 letra; não havia limite mínimo |
| E-mail | Formato básico | Aceitava `fulano@empresa`, sem domínio completo |
| Data de nascimento | **Não existia** | Não havia como saber a idade nem exigir maioridade |
| CPF | **Não existia** | Não havia documento para identificar a pessoa |
| Celular | **Não existia** | Sem contato para o atendimento |
| CEP | **Não existia** | Sem endereço |
| Validação | Regras repetidas em cada controller | O "Cadastre-se" e o administrador validavam de jeitos diferentes |

### Registro de incidente

| Campo | Como estava | Problema |
|---|---|---|
| Descrição resumida | Obrigatória, até 255 caracteres | Aceitava 1 caractere ("x"); espaços sobrando eram gravados |
| Descrição | Obrigatória, até 5000 caracteres | Aceitava "a"; não havia mínimo para explicar o problema |
| Problema começou em | **Não existia** | Não se sabia desde quando o problema acontecia |
| Prazo para resolver | **Não existia** | Não havia como saber se um incidente estava atrasado |

**Nos dois formulários:** não havia JavaScript; o usuário só via o erro depois de enviar.

## 3. O que foi feito (DEPOIS)

### 3.1 Regras implementadas — Cadastro de usuários

| Item | Campo | Regras aplicadas |
|---|---|---|
| 1.1 | **Nome** | Obrigatório; de 3 a 100 caracteres; recusa vazio ou só espaços; remove espaços no início, no fim e repetidos no meio |
| 1.2 | **E-mail** | Obrigatório; formato válido (`nome@dominio.com`); sem espaços; gravado em minúsculas; único |
| 1.3 | **Idade** | Calculada pela data de nascimento: mínimo de 18 e máximo de 100 anos |
| 2.1 *(obrigatório)* | **Data de nascimento** | Obrigatória; só datas que existem (recusa 31/02/2000 e 29/02 em ano não bissexto); não aceita data futura; exige 18 anos completos |
| 2.2 *(obrigatório)* | **CPF** | Obrigatório; com ou sem máscara; exatamente 11 números; confere os 2 dígitos verificadores; recusa sequências iguais (111.111.111-11); único; mensagem própria quando inválido |
| 2.3 | **CEP** | Obrigatório; `12345678` ou `12345-678`; exatamente 8 números; recusa letras e outros símbolos; gravado só com números |
| 2.4 | **Celular** | Obrigatório; DDD + número; 11 números; começa com 9 depois do DDD; com ou sem máscara; recusa letras e quantidade errada |

### 3.2 Regras implementadas — Registro de incidente

| Item | Campo | Regras aplicadas |
|---|---|---|
| 1.1 | **Descrição resumida** | Obrigatória; de 3 a 100 caracteres; recusa vazio ou só espaços; remove espaços no início, no fim e repetidos no meio |
| 1.1 | **Descrição** | Obrigatória; de 10 a 5000 caracteres; recusa só espaços; remove espaços do início e do fim (mantém as quebras de linha) |
| 3.1 | **Problema começou em** (data inicial) | Obrigatória; data que existe no calendário; não pode ser futura |
| 3.1 | **Prazo para resolver** (data final) | Obrigatório; data que existe; **não pode ser anterior à data inicial** (mensagem de período inconsistente); **permite a mesma data** (período de um único dia); num incidente novo, não pode ser uma data que já passou |

**Regra de negócio extra:** a tela do incidente mostra o período e um selo vermelho **"Atrasado"** quando o prazo passou e o incidente ainda não foi resolvido. Nos incidentes que já existiam, o período foi preenchido automaticamente: início = data de abertura; prazo pela prioridade (P1 = 1 dia, P2 = 2, P3 = 5, P4 = 10, P5 = 30 dias).

### 3.3 Arquivos criados ou alterados

| Arquivo | O que faz |
|---|---|
| `app/Rules/Cpf.php` | **Novo.** Regra do CPF: calcula os dígitos verificadores e recusa sequências repetidas |
| `app/Http/Requests/UsuarioRequest.php` | **Novo.** Regras e mensagens do cadastro de usuários. Normaliza (tira espaços e máscaras) antes de validar |
| `app/Http/Requests/ChamadoRequest.php` | Regras de texto e de período do incidente, com mensagens próprias |
| `app/Http/Controllers/UserController.php` e `AuthController.php` | Inclusão, alteração e "Cadastre-se" usam o mesmo `UsuarioRequest` |
| `database/migrations/2026_11_10_000001_add_dados_pessoais_to_users_table.php` | **Novo.** Colunas `cpf` (única), `data_nascimento`, `celular` e `cep` |
| `database/migrations/2026_11_10_000002_add_periodo_to_chamados_table.php` | **Novo.** Colunas `inicio_problema` e `prazo`, preenchidas nos incidentes antigos |
| `app/Models/User.php` | Mostra CPF, celular e CEP com máscara; calcula a idade |
| `app/Models/Chamado.php` | Novos campos; prazo sugerido por prioridade; método `atrasado()` |
| `resources/views/usuarios/_dados_pessoais.blade.php` | **Novo.** Campos pessoais usados nos dois formulários de usuário |
| `resources/views/usuarios/*`, `auth/register.blade.php` | Campos novos e área para as mensagens de erro |
| `resources/views/chamados/_form.blade.php`, `show.blade.php` | Campos do período, mensagens de erro e selo "Atrasado" |
| `public/js/validacao.js` | **Novo.** Máscaras e avisos na hora para os dois formulários (complemento) |
| `tests/Feature/ValidacaoCadastroTest.php` | **Novo.** 48 testes do cadastro de usuários |
| `tests/Feature/ValidacaoChamadoTest.php` | **Novo.** 28 testes do incidente |

### 3.4 Como a validação funciona

```
Formulário (HTML + JS)  →  Servidor (Laravel: UsuarioRequest / ChamadoRequest)  →  Banco (só dados válidos)
        ↑                              │
        └──── erro: volta ao formulário com a mensagem
```

1. **No navegador (JavaScript):** coloca a máscara enquanto a pessoa digita, avisa o erro ao sair do campo e não deixa enviar com erro. No período, o calendário do prazo não deixa escolher dia anterior ao início. É só para ajudar o usuário.
2. **No servidor (Laravel):** os Form Requests normalizam e conferem tudo de novo. Com erro, voltam ao formulário com a mensagem e **nada é gravado**. Essa é a validação que garante os dados, mesmo com o JavaScript desligado.
3. **No banco:** o CPF tem índice único; CPF, CEP e celular são guardados só com números.

## 4. Testes realizados

Comando: `php artisan test`

**Resultado: 93 testes aprovados (470 verificações).** São 76 testes novos de validação (48 do usuário e 28 do incidente), e os 17 que já existiam continuam passando. A data de "hoje" fica fixa em 10/11/2026 nos testes, para os resultados não mudarem com o tempo.

### 4.1 Cadastro de usuários — valores válidos e limites (devem ser ACEITOS)

| Caso | Valor | Resultado |
|---|---|---|
| Tudo com máscara | CPF `529.982.247-25`, celular `(11) 98765-4321`, CEP `01310-100` | ✅ Aceito; gravado `52998224725`, `11987654321`, `01310100` |
| Tudo sem máscara | `52998224725`, `11987654321`, `01310100` | ✅ Aceito |
| Espaços sobrando | `"   Fulano    de   Tal  "` | ✅ Gravado `"Fulano de Tal"` |
| E-mail com maiúsculas | `" FULANO@Empresa.com "` | ✅ Gravado `fulano@empresa.com` |
| Nome com 3 e com 100 caracteres | `Ana` / 100 letras | ✅ Aceito |
| Faz 18 anos hoje | `10/11/2008` | ✅ Aceito |
| Tem exatamente 100 anos | `10/11/1926` | ✅ Aceito |
| Nascido em 29/02 de ano bissexto | `29/02/2000` | ✅ Aceito |
| CPF com dígito verificador 0 | `111.444.777-35` | ✅ Aceito |
| Alteração mantendo o próprio CPF | mesmo CPF | ✅ Aceito (não acusa duplicado) |

### 4.2 Cadastro de usuários — valores inválidos (RECUSADOS, com mensagem, sem gravar)

| Campo | Valor testado | Mensagem exibida |
|---|---|---|
| Nome | vazio / só espaços | O campo nome é obrigatório. |
| Nome | `Al` | O nome deve ter pelo menos 3 letras. |
| Nome | 101 caracteres | O nome pode ter no máximo 100 caracteres. |
| E-mail | `fulano.empresa.com`, `fulano@`, `fulano@empresa`, `ful ano@empresa.com` | Informe um e-mail válido, por exemplo: nome@empresa.com. |
| Nascimento | vazio | O campo data de nascimento é obrigatório. |
| Nascimento | `31/02/2000`, `29/02/2001`, `abc`, formato errado | Informe uma data de nascimento que exista no calendário (dia/mês/ano). |
| Nascimento | `01/01/2027` | A data de nascimento não pode estar no futuro. |
| Nascimento | `11/11/2008` (faz 18 amanhã) | É preciso ter pelo menos 18 anos completos. |
| Nascimento | `10/11/1925` (101 anos) | A idade máxima permitida é de 100 anos. Confira o ano de nascimento. |
| CPF | vazio | O campo CPF é obrigatório. |
| CPF | `529.982.247-26`, `111.111.111-11`, `00000000000` | O CPF informado é inválido. Confira os números digitados. |
| CPF | 10 números, 12 números, com letra | O CPF deve ter exatamente 11 números (pode digitar com ou sem pontos e traço). |
| CPF | já usado por outro usuário | Já existe um usuário cadastrado com este CPF. |
| CEP | `1234567`, `1234-5678`, `12345-67a`, `12.345-678` | O CEP deve estar no formato 12345-678 ou 12345678, somente com números. |
| Celular | `(11) 8765-4321`, 12 números, sem DDD, com letra | O celular deve ter DDD + 9 números, por exemplo: (11) 98765-4321. |
| Celular | `11887654321` | O número do celular deve começar com 9 logo depois do DDD. |

Testado na **inclusão** e na **alteração** pelo administrador e no **"Cadastre-se"**.

### 4.3 Incidente — valores válidos e limites (devem ser ACEITOS)

| Caso | Valor | Resultado |
|---|---|---|
| Espaços sobrando no título | `"   Impressora    não   imprime  "` | ✅ Gravado `"Impressora não imprime"` |
| Título com 3 e com 100 caracteres | `VPN` / 100 letras | ✅ Aceito |
| Descrição com 10 caracteres | `1234567890` | ✅ Aceito |
| Começou hoje | início `10/11/2026` | ✅ Aceito |
| **Período de um único dia** | início = prazo = `10/11/2026` | ✅ Aceito |
| Começou há meses, prazo hoje | `01/06/2026` até `10/11/2026` | ✅ Aceito |
| Início em 29/02 de ano bissexto | `29/02/2024` | ✅ Aceito |
| Alteração de incidente antigo já atrasado | prazo `05/10/2026` | ✅ Aceito; o incidente aparece como "Atrasado" |

### 4.4 Incidente — valores inválidos (RECUSADOS, com mensagem, sem gravar)

| Campo | Valor testado | Mensagem exibida |
|---|---|---|
| Descrição resumida | vazio / só espaços | O campo descrição resumida é obrigatório. |
| Descrição resumida | `PC` | A descrição resumida deve ter pelo menos 3 caracteres. |
| Descrição resumida | 101 caracteres | A descrição resumida pode ter no máximo 100 caracteres. |
| Descrição | vazio / só espaços e quebras de linha | O campo descrição é obrigatório. |
| Descrição | `123456789` (9 caracteres) | Explique melhor o problema: escreva pelo menos 10 caracteres. |
| Descrição | 5001 caracteres | O campo descrição não pode ter mais de 5000 caracteres. |
| Problema começou em | vazio | O campo problema começou em é obrigatório. |
| Problema começou em | `30/02/2026`, `ontem` | Informe uma data de início que exista no calendário (dia/mês/ano). |
| Problema começou em | `11/11/2026` (amanhã) | O problema não pode ter começado numa data futura. |
| Prazo | vazio | O campo prazo para resolver é obrigatório. |
| Prazo | `31/11/2026` | Informe um prazo que exista no calendário (dia/mês/ano). |
| **Prazo** | início `09/11` e prazo `08/11` | **Período inconsistente: o prazo não pode ser anterior à data em que o problema começou.** |
| Prazo (incidente novo) | `05/11/2026` | O prazo para resolver não pode ser uma data que já passou. |

Testado no **registro** e na **alteração** do incidente.

### 4.5 Teste manual no navegador

1. **Usuário, com JavaScript:** com `Al`, `fulano@empresa`, nascimento `01/01/2010`, CPF `52998224726`, celular `11887654321` e CEP `1234567`, cada campo ficou vermelho com a sua mensagem e o formulário não foi enviado. A idade apareceu calculada ("Idade: 16 anos").
2. **Usuário, sem passar pelo JavaScript** (envio forçado pelo console, com CPF `111.111.111-11`): o **servidor recusou** e voltou ao formulário com as mesmas mensagens. Nada foi gravado.
3. **Incidente, com JavaScript:** com início `09/11/2026`, prazo `08/11/2026`, título `PC` e descrição `curto`, apareceram as 3 mensagens, incluindo "Período inconsistente".
4. **Incidente, sem passar pelo JavaScript:** o **servidor recusou** com as mesmas 3 mensagens. Nenhum incidente foi criado.
5. **Tela do incidente:** mostra "26/10/2026 até 28/10/2026" e o selo **Atrasado** num incidente ainda em andamento.

## 5. Critérios de aceitação

| Critério | Situação |
|---|---|
| Identificar os formulários | ✅ Cadastro de usuários e registro de incidente (seção 1) |
| Campos validados corretamente | ✅ 7 campos no usuário e 4 no incidente |
| Mensagens claras e específicas | ✅ Uma mensagem para cada tipo de erro, em português |
| Dados inválidos não são gravados | ✅ Comprovado pelos testes |
| Validação na inclusão e na alteração | ✅ Testado nos dois formulários |
| Validação no servidor (PHP/Laravel) | ✅ `UsuarioRequest`, `ChamadoRequest` e `App\Rules\Cpf` |
| JavaScript como complemento | ✅ `public/js/validacao.js` |
| Testes válidos, inválidos e de limite | ✅ 76 testes novos, todos aprovados |
| Documentação antes e depois | ✅ Este documento |

## 6. Como testar

```bash
php artisan migrate
php artisan test --filter=Validacao
```

Pelo navegador:
- **Usuário:** `/cadastro` ou, como administrador, **Administração → Usuários → Novo usuário**. CPFs válidos para teste: `529.982.247-25` e `111.444.777-35`.
- **Incidente:** **Registrar incidente**. Tente colocar o prazo antes da data em que o problema começou.

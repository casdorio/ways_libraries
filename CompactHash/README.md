Claro! Aqui está um exemplo de `README.md` bem completo e fácil de entender, explicando como usar o `CompactHash`:

---

## 🔐 CompactHash

Uma biblioteca leve e sem dependências externas para gerar **hashes compactos** e seguros a partir de números inteiros. Totalmente feita em PHP 8.3+ com suporte a configuração via `.env`.

---

### 📦 Instalação

Clone o repositório ou copie os arquivos para seu projeto.

```bash
git clone https://github.com/seu-usuario/compact-hash.git
```

> ✅ Sem dependências externas (não usa Hashids original)

---

### ⚙️ Configuração

Crie um arquivo `.env` na raiz do seu projeto com as variáveis:

```dotenv
# Ativa ou desativa a criptografia
ENCRYPTION=true

# Palavra-chave para gerar o hash
HASHIDS_SALT=casdorio_salt

# Multiplicador usado na criptografia
HASHIDS_KEY=7

# Tamanho mínimo do hash
HASHIDS_LENGTH=12
```

> Para testes ou produção, crie seu próprio `.env` com valores reais.

---

### 🧠 Como funciona

A biblioteca multiplica o número original pelo `HASHIDS_KEY`, e então converte para hash. O processo inverso faz a divisão e retorna o valor original.

---

### 🛠️ Exemplos de Uso

#### ✅ Encriptar número

```php
use Casdorio\CompactHash\CompactHash;

$hash = CompactHash::encrypt(123); // Ex: "Nk3Lx2zvQ1A"
```

#### ✅ Decriptar número

```php
use Casdorio\CompactHash\CompactHash;

$original = CompactHash::decrypt('Nk3Lx2zvQ1A'); // 123
```

---

### 🧩 Trabalhar com arrays

#### 🔒 Encriptar campos específicos de um array

```php
$data = [
    'id' => 123,
    'name' => 'Carlos',
    'info' => ['id' => 456]
];

$encrypted = CompactHash::encryptArray($data, ['id']);
```

#### 🔓 Decriptar os campos

```php
$decrypted = CompactHash::decryptArray($encrypted, ['id']);
```

---

### 🔁 Fallback automático

- Se `ENCRYPTION=false`, os métodos retornam os valores originais, sem criptografia.
- Funciona com IDs simples ou arrays multidimensionais.

---

### 📁 Estrutura da pasta

```
compact-hash/
├── src/
│   └── CompactHash.php
├── .env.example
└── README.md
```

---

### 🤝 Licença

MIT - Livre para uso pessoal e comercial.

---

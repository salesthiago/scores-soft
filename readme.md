# 📌 Programa de Pontuação e Premiação para Clientes

Bem-vindo ao **Programa de Pontuação e Premiação para Vendas**! Este projeto tem como objetivo permitir que clientes acumulem pontos com suas compras e possam trocá-los por prêmios.

## 🚀 Objetivo do Projeto
Este sistema permite que os clientes se cadastrem utilizando seu **número de celular, nome e senha**. A partir disso, suas compras gerarão pontos, que poderão ser trocados por prêmios.

---

## ⚙️ Requisitos
Para rodar o projeto corretamente, é necessário ter os seguintes requisitos instalados:

- [PHP 8.3](https://www.php.net/downloads.php)
- [Node.js 20](https://nodejs.org/en/)
- [Composer](https://getcomposer.org/)
- [NPM](https://www.npmjs.com/)

---

## 🔧 Instalação
Siga os passos abaixo para configurar o ambiente e rodar o projeto:

### Backend (Symfony)
1. Clone o repositório:
   ```bash
   git clone https://github.com/seu-repositorio.git
   cd seu-repositorio
   ```
2. Instale as dependências do Symfony:
   ```bash
   composer install
   ```
3. Configure as variáveis de ambiente no arquivo `.env`.
4. Execute as migrações do banco de dados:
   ```bash
   php bin/console doctrine:migrations:migrate
   ```
5. Crie as tabelas do banco
   ```bash
   php bin/console doctrine:migrations:migrate
   ```
6. Inicie o servidor de desenvolvimento:
   ```bash
   symfony server:start
   ```

### Frontend
1. Instale as dependências do projeto:
   ```bash
   npm install
   ```
2. Compile os arquivos para produção:
   ```bash
   npm run prod
   ```

---

## 🏆 Funcionalidades Principais
✅ Cadastro de clientes (número de celular, nome e senha).  
✅ Acúmulo de pontos a cada compra.  
✅ Troca de pontos por prêmios.  
✅ Painel administrativo para gestão de usuários e recompensas.

---

## 🤝 Contribuição
Sinta-se à vontade para contribuir com melhorias e sugestões. Para isso:
1. Faça um **fork** do projeto.
2. Crie uma nova **branch** para sua funcionalidade: `git checkout -b minha-nova-feature`
3. Faça o **commit** das suas alterações: `git commit -m 'Adiciona nova funcionalidade X'`
4. Envie para o repositório: `git push origin minha-nova-feature`
5. Abra um **Pull Request**.

---

## 📜 Licença
Este projeto é licenciado sob a **MIT License** - veja o arquivo [LICENSE](LICENSE) para mais detalhes.

🚀 **Vamos juntos criar um programa de recompensas incrível!** 🎉


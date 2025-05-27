# Zapix - Módulo Zabbix API Tool

Este é o módulo Zapix para Zabbix 6.0+, uma ferramenta online para testar e explorar a API do Zabbix diretamente na interface web do Zabbix.

## Características

- **Interface integrada**: Acesso direto através do menu de Administração do Zabbix
- **Editor de código**: Editor ACE com syntax highlighting para JSON
- **Autocomplete**: Sugestões automáticas para métodos da API
- **Gerenciamento de conexões**: Salva e carrega configurações de conexão
- **Histórico de requisições**: Salva e reutiliza requisições frequentes
- **Compatibilidade**: Suporte para Zabbix 6.0 e versões superiores

## Instalação

1. Copie todo o diretório `mnzguruapi` para `/usr/share/zabbix/modules/`
2. Certifique-se de que as permissões estão corretas:
   ```bash
   chown -R www-data:www-data /usr/share/zabbix/modules/mnzguruapi
   chmod -R 755 /usr/share/zabbix/modules/mnzguruapi
   ```
3. Reinicie o servidor web (Apache/Nginx)
4. Acesse o Zabbix e vá para **Administração > Zapix API Tool**

## Estrutura do Módulo

```
mnzguruapi/
├── manifest.json           # Configuração do módulo
├── Module.php             # Classe principal do módulo
├── actions/
│   └── ZapixView.php      # Controlador da view
├── views/
│   └── mnzguruapi.view.php     # Template da interface
├── assets/
│   ├── css/
│   │   ├── mnzguruapi-module.css      # Estilos específicos do módulo
│   │   ├── style.css             # Estilos originais
│   │   └── bootstrap/
│   │       └── bootstrap.min.css
│   └── js/
│       ├── mnzguruapi-module.js       # JavaScript específico do módulo
│       ├── js.js                 # Lógica principal
│       ├── jsonrpc.js           # Cliente JSON-RPC
│       ├── jsonlint.js          # Validador JSON
│       ├── ace.js               # Editor de código
│       ├── mode-json.js         # Modo JSON para ACE
│       ├── theme-github.js      # Tema GitHub para ACE
│       ├── bootstrap.min.js     # Bootstrap JavaScript
│       ├── typeahead.bundle.min.js # Autocomplete
│       └── clipboard.min.js     # Funcionalidade de copiar
└── README-MODULE.md        # Este arquivo
```

## Uso

1. **Conectar**: Clique em "Connect" para configurar a conexão com a API
2. **Método**: Digite ou selecione um método da API (ex: `host.get`)
3. **Parâmetros**: Insira os parâmetros JSON no editor
4. **Executar**: Clique em "Execute" para fazer a chamada
5. **Resultado**: Veja a requisição e resposta nas áreas correspondentes

## Exemplos de Uso

### Listar hosts
```json
{
    "output": ["hostid", "host", "name"],
    "limit": 10
}
```

### Buscar problemas
```json
{
    "output": "extend",
    "recent": true,
    "limit": 50
}
```

### Obter informações do usuário
```json
{
    "output": "extend"
}
```

## Funcionalidades Avançadas

- **Salvar requisições**: Use o botão "Save" para salvar requisições frequentes
- **Carregar requisições**: Use o botão "Load" para reutilizar requisições salvas
- **Autocomplete**: Digite parte do nome do método para ver sugestões
- **Validação JSON**: O editor valida automaticamente a sintaxe JSON

## Troubleshooting

### Módulo não aparece no menu
- Verifique se o arquivo `manifest.json` está correto
- Confirme as permissões dos arquivos
- Reinicie o servidor web

### Erro de permissões
- Certifique-se de que o usuário tem permissão para gerenciar tokens de API
- Verifique as configurações de role no Zabbix

### JavaScript não funciona
- Verifique se todos os arquivos JS estão presentes
- Confirme que não há erros no console do navegador
- Verifique se os caminhos no `manifest.json` estão corretos

## Desenvolvimento

Para modificar o módulo:

1. **CSS**: Edite `assets/css/mnzguruapi-module.css` para estilos específicos
2. **JavaScript**: Modifique `assets/js/mnzguruapi-module.js` para funcionalidades
3. **PHP**: Altere `actions/ZapixView.php` para lógica do backend
4. **Interface**: Modifique `views/mnzguruapi.view.php` para o layout

## Compatibilidade

- **Zabbix**: 6.0+
- **PHP**: 7.4+
- **Navegadores**: Chrome, Firefox, Safari, Edge (versões modernas)

## Licença

Este módulo mantém a mesma licença do projeto original Zapix.

## Contribuição

Para contribuir com melhorias:

1. Fork o projeto
2. Crie uma branch para sua feature
3. Faça commit das mudanças
4. Abra um Pull Request

## Suporte

Para suporte e questões:
- Abra uma issue no repositório GitHub
- Consulte a documentação oficial da API do Zabbix
- Verifique os logs do servidor web para erros 
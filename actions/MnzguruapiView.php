<?php

namespace Modules\Mnzguruapi\Actions;

use CController,
    CControllerResponseData,
    CControllerResponseFatal,
    API,
    CRoleHelper,
    CWebUser,
    Exception;

class MnzguruapiView extends CController {
    
    protected function init(): void {
        $this->disableCsrfValidation();
    }

    protected function checkInput(): bool {
        $fields = [
            'action' => 'string',
            'method' => 'string',
            'params' => 'string',
            'host' => 'string',
            'login' => 'string',
            'password' => 'string'
        ];

        $ret = $this->validateInput($fields);

        if (!$ret) {
            $this->handleInvalidInput();
        }

        return $ret;
    }

    protected function checkPermissions(): bool {
        // Permite acesso para usuários logados (mais permissivo para teste)
        return $this->getUserType() >= USER_TYPE_ZABBIX_USER;
    }

    protected function doAction(): void {
        try {
            // Se for uma requisição AJAX para executar API
            if ($this->hasInput('method') && $this->hasInput('params')) {
                $this->handleApiRequest();
                return;
            }

            // Renderiza a página principal
            $response_data = [
                'title' => _('MonzGuru - API Tool'),
                'user' => CWebUser::$data,
                'zabbix_version' => ZABBIX_VERSION
            ];

            $response = new CControllerResponseData($response_data);
            $this->setResponse($response);
        }
        catch (Exception $e) {
            $response = new CControllerResponseFatal();
            $response->setTitle(_('Error'));
            $response->setMessage($e->getMessage());
            $this->setResponse($response);
        }
    }

    protected function handleInvalidInput(): void {
        $response = new CControllerResponseFatal();
        $response->setTitle(_('Invalid Input'));
        $response->setMessage(_('Invalid input parameters provided.'));
        $this->setResponse($response);
    }

    protected function handleApiRequest(): void {
        try {
            $method = $this->getInput('method');
            $params = $this->getInput('params');

            // Decodifica os parâmetros JSON
            $decoded_params = json_decode($params, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception(_('Invalid JSON parameters'));
            }

            // Executa a chamada da API usando a API interna do Zabbix
            $result = $this->executeZabbixApiCall($method, $decoded_params);

            $response = [
                'jsonrpc' => '2.0',
                'result' => $result,
                'id' => 1
            ];

            header('Content-Type: application/json');
            echo json_encode($response);
            exit;

        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'jsonrpc' => '2.0',
                'error' => [
                    'code' => -1,
                    'message' => $e->getMessage(),
                    'data' => $e->getTraceAsString()
                ],
                'id' => 1
            ]);
            exit;
        }
    }

    protected function executeZabbixApiCall($method, $params): array {
        // Separa o método em objeto e ação
        $methodParts = explode('.', $method, 2);
        if (count($methodParts) !== 2) {
            throw new Exception(_('Invalid API method format. Expected: object.action'));
        }

        $object = $methodParts[0];
        $action = $methodParts[1];

        // Verifica permissões específicas para o método
        $this->checkApiMethodPermissions($object, $action);

        // Log da tentativa de acesso à API
        $this->logApiAccess($method, $params);

        // Mapeia os objetos da API para as classes correspondentes
        $apiObjects = [
            'host' => 'Host',
            'hostgroup' => 'HostGroup', 
            'item' => 'Item',
            'trigger' => 'Trigger',
            'user' => 'User',
            'usergroup' => 'UserGroup',
            'template' => 'Template',
            'graph' => 'Graph',
            'event' => 'Event',
            'problem' => 'Problem',
            'alert' => 'Alert',
            'action' => 'Action',
            'maintenance' => 'Maintenance',
            'map' => 'Map',
            'screen' => 'Screen',
            'script' => 'Script',
            'proxy' => 'Proxy',
            'drule' => 'DRule',
            'dhost' => 'DHost',
            'dservice' => 'DService',
            'httptest' => 'HttpTest',
            'application' => 'Application',
            'service' => 'Service',
            'sla' => 'SLA',
            'correlation' => 'Correlation',
            'dashboard' => 'Dashboard',
            'mediatype' => 'MediaType',
            'valuemap' => 'ValueMap',
            'iconmap' => 'IconMap',
            'image' => 'Image',
            'usermacro' => 'UserMacro',
            'hostinterface' => 'HostInterface',
            'discoveryrule' => 'DiscoveryRule',
            'itemprototype' => 'ItemPrototype',
            'triggerprototype' => 'TriggerPrototype',
            'graphprototype' => 'GraphPrototype',
            'hostprototype' => 'HostPrototype',
            'trend' => 'Trend',
            'history' => 'History',
            'task' => 'Task',
            'token' => 'Token',
            'role' => 'Role',
            'settings' => 'Settings',
            'housekeeping' => 'Housekeeping',
            'auditlog' => 'AuditLog',
            'authentication' => 'Authentication',
            'autoregistration' => 'Autoregistration',
            'regexp' => 'Regexp',
            'report' => 'Report',
            'templatedashboard' => 'TemplateDashboard',
            'configuration' => 'Configuration',
            'apiinfo' => 'APIInfo'
        ];

        if (!isset($apiObjects[$object])) {
            throw new Exception(_('Unsupported API object: ') . $object);
        }

        $apiClass = $apiObjects[$object];

        // Executa a chamada da API
        try {
            switch ($action) {
                case 'get':
                    $result = API::$apiClass()->get($params);
                    break;
                case 'create':
                    $result = API::$apiClass()->create($params);
                    break;
                case 'update':
                    $result = API::$apiClass()->update($params);
                    break;
                case 'delete':
                    $result = API::$apiClass()->delete($params);
                    break;
                case 'version':
                    if ($object === 'apiinfo') {
                        $result = ZABBIX_VERSION;
                    } else {
                        throw new Exception(_('Version method only available for apiinfo'));
                    }
                    break;
                default:
                    // Para métodos específicos, tenta chamar diretamente
                    if (method_exists(API::$apiClass(), $action)) {
                        $result = call_user_func([API::$apiClass(), $action], $params);
                    } else {
                        throw new Exception(_('Unsupported API action: ') . $action . ' for object: ' . $object);
                    }
                    break;
            }

            return $result;

        } catch (Exception $e) {
            throw new Exception(_('API call failed: ') . $e->getMessage());
        }
    }

    /**
     * Verifica se o usuário tem permissão para executar o método da API
     */
    protected function checkApiMethodPermissions(string $object, string $action): void {
        $userType = $this->getUserType();
        $userId = CWebUser::$data['userid'] ?? null;

        // Super Admin pode tudo
        if ($userType == USER_TYPE_SUPER_ADMIN) {
            return;
        }

        // Verifica permissões baseadas no tipo de usuário e ação
        $this->checkObjectPermissions($object, $action, $userType);
        
        // Verifica permissões específicas do role do usuário
        if ($userId) {
            $this->checkRolePermissions($object, $action, $userId);
        }
    }

    /**
     * Verifica permissões baseadas no tipo de usuário
     */
    protected function checkObjectPermissions(string $object, string $action, int $userType): void {
        // Ações que apenas Super Admin pode executar
        $superAdminOnly = [
            'user' => ['create', 'update', 'delete'],
            'usergroup' => ['create', 'update', 'delete'],
            'authentication' => ['get', 'update'],
            'settings' => ['get', 'update'],
            'housekeeping' => ['get', 'update'],
            'autoregistration' => ['get', 'update'],
            'role' => ['create', 'update', 'delete'],
            'token' => ['create', 'update', 'delete', 'generate']
        ];

        // Ações que Admin pode executar (além das do usuário)
        $adminActions = [
            'host' => ['create', 'update', 'delete', 'massadd', 'massremove', 'massupdate'],
            'hostgroup' => ['create', 'update', 'delete', 'massadd', 'massremove', 'massupdate'],
            'template' => ['create', 'update', 'delete', 'massadd', 'massremove', 'massupdate'],
            'item' => ['create', 'update', 'delete'],
            'trigger' => ['create', 'update', 'delete', 'adddependencies', 'deletedependencies'],
            'graph' => ['create', 'update', 'delete'],
            'action' => ['create', 'update', 'delete'],
            'maintenance' => ['create', 'update', 'delete'],
            'mediatype' => ['create', 'update', 'delete'],
            'script' => ['create', 'update', 'delete', 'execute'],
            'proxy' => ['create', 'update', 'delete'],
            'drule' => ['create', 'update', 'delete'],
            'correlation' => ['create', 'update', 'delete'],
            'dashboard' => ['create', 'update', 'delete'],
            'map' => ['create', 'update', 'delete'],
            'regexp' => ['create', 'update', 'delete'],
            'valuemap' => ['create', 'update', 'delete'],
            'iconmap' => ['create', 'update', 'delete'],
            'image' => ['create', 'update', 'delete'],
            'usermacro' => ['create', 'update', 'delete', 'createglobal', 'updateglobal', 'deleteglobal'],
            'configuration' => ['export', 'import', 'importcompare']
        ];

        // Verifica se é uma ação restrita ao Super Admin
        if (isset($superAdminOnly[$object]) && in_array($action, $superAdminOnly[$object])) {
            throw new Exception(_('Access denied. Super Admin privileges required for this operation.'));
        }

        // Verifica se usuário comum está tentando fazer ação de Admin
        if ($userType == USER_TYPE_ZABBIX_USER) {
            if (isset($adminActions[$object]) && in_array($action, $adminActions[$object])) {
                throw new Exception(_('Access denied. Admin privileges required for this operation.'));
            }
        }

        // Ações específicas que requerem permissões especiais
        $this->checkSpecialPermissions($object, $action, $userType);
    }

    /**
     * Verifica permissões especiais para ações específicas
     */
    protected function checkSpecialPermissions(string $object, string $action, int $userType): void {
        // Script execution requer permissão especial
        if ($object === 'script' && $action === 'execute') {
            if ($userType < USER_TYPE_ZABBIX_ADMIN) {
                throw new Exception(_('Access denied. Script execution requires Admin privileges.'));
            }
        }

        // Operações de configuração são críticas
        if ($object === 'configuration') {
            if ($userType < USER_TYPE_ZABBIX_ADMIN) {
                throw new Exception(_('Access denied. Configuration operations require Admin privileges.'));
            }
        }

        // Operações em usuários são sensíveis
        if ($object === 'user' && in_array($action, ['create', 'update', 'delete', 'unblock'])) {
            if ($userType < USER_TYPE_SUPER_ADMIN) {
                throw new Exception(_('Access denied. User management requires Super Admin privileges.'));
            }
        }

        // Auditlog é apenas para visualização por admins
        if ($object === 'auditlog') {
            if ($userType < USER_TYPE_ZABBIX_ADMIN) {
                throw new Exception(_('Access denied. Audit log access requires Admin privileges.'));
            }
        }
    }

    /**
     * Verifica permissões baseadas no role do usuário
     */
    protected function checkRolePermissions(string $object, string $action, string $userId): void {
        try {
            // Obtém informações do role do usuário
            $userRoles = API::User()->get([
                'output' => ['roleid'],
                'userids' => [$userId],
                'selectRole' => ['name', 'type']
            ]);

            if (empty($userRoles)) {
                throw new Exception(_('User role information not found.'));
            }

            $userRole = $userRoles[0]['role'];
            $roleType = $userRole['type'] ?? USER_TYPE_ZABBIX_USER;

            // Verifica se o role permite a operação
            $this->validateRoleAccess($object, $action, $roleType, $userRole['name']);

        } catch (Exception $e) {
            // Se não conseguir verificar o role, usa verificação básica por tipo de usuário
            error_log('MonzGuru API: Could not verify role permissions: ' . $e->getMessage());
        }
    }

    /**
     * Valida acesso baseado no role específico
     */
    protected function validateRoleAccess(string $object, string $action, int $roleType, string $roleName): void {
        // Roles customizados podem ter restrições específicas
        if (strpos(strtolower($roleName), 'readonly') !== false || 
            strpos(strtolower($roleName), 'viewer') !== false) {
            
            // Roles de apenas leitura só podem fazer GET
            if (!in_array($action, ['get', 'version'])) {
                throw new Exception(_('Access denied. Read-only role cannot perform write operations.'));
            }
        }

        // Verifica restrições específicas por objeto para roles limitados
        if ($roleType == USER_TYPE_ZABBIX_USER) {
            $restrictedObjects = ['user', 'usergroup', 'role', 'authentication', 'settings'];
            if (in_array($object, $restrictedObjects)) {
                throw new Exception(_('Access denied. Insufficient privileges to access this resource.'));
            }
        }
    }

    /**
     * Registra log de acesso à API para auditoria
     */
    protected function logApiAccess(string $method, array $params): void {
        $userId = CWebUser::$data['userid'] ?? 'unknown';
        $username = CWebUser::$data['username'] ?? 'unknown';
        $userType = $this->getUserType();
        
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'user_id' => $userId,
            'username' => $username,
            'user_type' => $userType,
            'method' => $method,
            'params_count' => count($params),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];

        // Log para arquivo (pode ser configurado para usar syslog ou banco)
        error_log('MonzGuru API Access: ' . json_encode($logData));
    }
} 
<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Helpers\TokenHelper;
use App\Services\OpenAiService;
use App\Services\UserManager;

class ChatController extends BaseController
{
    protected OpenAiService $openAiService;
    protected UserManager $userManager;

    public function __construct()
    {
        $this->openAiService = new OpenAiService();
        $this->userManager = new UserManager();
    }

    private function validateToken(): ?bool
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        $user = TokenHelper::validateToken($authHeader);

        if (!$user)
            return false;
        return true;
    }

    public function message()
    {
        if (!$this->validateToken())
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);

        $data = $this->request->getJSON(true);
        if (!isset($data['message'])) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => 'Missing message']);
        }

        $response = $this->openAiService->ask($data['message']);

        $choice = $response['choices'][0]['message'];

        if (isset($choice['function_call'])) {
            $func = $choice['function_call'];
            $funcName = $func['name'];
            $args = json_decode($func['arguments'], true);

            try {
                switch ($funcName) {
                    case 'createUser':
                        $user = $this->userManager->create($args);
                        $message = "✅ User '{$user['name']}' created successfully!";
                        break;
            
                    case 'updateUser':
                        $user = $this->userManager->update($args['id'], $args);
                        $message = "✅ User #{$user['id']} updated!";
                        break;
            
                    case 'deleteUser':
                        $this->userManager->delete($args['id']);
                        $message = "🗑️ User #{$args['id']} deleted.";
                        break;
            
                    default:
                        $message = "⚠️ Function '$funcName' is not supported yet.";
                }
            
                return $this->response->setJSON(['reply' => $message, 'action' => 'refresh']);
            } catch (\Exception $e) {
                return $this->response
                    ->setStatusCode(400)
                    ->setJSON(['reply' => "⚠️ Operation failed: " . $e->getMessage()]);
            }

            return $this->response->setJSON([
                'reply' => "Function '$funcName' recognized but not implemented yet."
            ]);
        }

        $text = $choice['content'] ?? "🤔 I'm not sure how to help with that.";
        return $this->response->setJSON(['reply' => $text]);
    }
}

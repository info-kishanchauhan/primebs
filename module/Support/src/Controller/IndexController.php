
<?php
namespace Support\Controller;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }

    public function submitAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();

            $name = $data['name'] ?? 'Unknown';
            $email = $data['email'] ?? 'noreply@primedigitalarena.in';
            $subject = $data['subject'] ?? 'Support Request';
            $support_type = $data['support_type'] ?? 'General';
            $ticket_id = 'PDA-TCK-' . date('Ymd') . '-' . rand(1000,9999);

            $body = "New support ticket:\n\n";
            $body .= "Ticket ID: $ticket_id\n";
            $body .= "Name: $name\nEmail: $email\n";
            $body .= "Support Type: $support_type\nSubject: $subject\n\n";

            foreach ($data as $key => $value) {
                if (!in_array($key, ['name','email','subject','support_type']) && !empty($value)) {
                    $body .= ucfirst(str_replace('_',' ', $key)) . ": $value\n";
                }
            }

            @mail('support@primedigitalarena.in', "🎫 New Ticket [$ticket_id]", $body, "From: support@primedigitalarena.in");
            $confirm = "Hi $name,\n\nYour ticket ID is $ticket_id regarding \"$subject\".\nWe’ll follow up shortly.\n\n- Prime Digital Arena Support";
            @mail($email, "✅ Ticket Received: $ticket_id", $confirm, "From: support@primedigitalarena.in");

            return new ViewModel(['message' => "✅ Ticket submitted successfully. Ref ID: $ticket_id"]);
        }
        return $this->redirect()->toRoute('support');
    }
}

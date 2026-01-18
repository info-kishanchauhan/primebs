<?php
namespace Faq\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
{
    $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');

    // Fetch latest articles
    $faqSql = "SELECT * FROM faq ORDER BY updated_at DESC";
    $faqResult = $adapter->createStatement($faqSql)->execute();
    $faqs = [];
    foreach ($faqResult as $row) {
        $faqs[] = $row;
    }

    // Determine visibility for categories
    $userType = isset($_SESSION['user_type']) ? strtolower(trim($_SESSION['user_type'])) : '';
    $isAdmin = (isset($_SESSION['STAFFUSER']) && $_SESSION['STAFFUSER'] == '1') || (isset($_SESSION['user_id']) && $_SESSION['user_id'] == '0');

    if ($isAdmin) {
        $allowedVisibilities = ['all', 'enterprise', 'admin'];
    } elseif ($userType === 'enterprise') {
        $allowedVisibilities = ['all', 'enterprise'];
    } else {
        $allowedVisibilities = ['all'];
    }

    $escapedVisibilities = array_map(function ($v) {
        return "'" . addslashes($v) . "'";
    }, $allowedVisibilities);
    $inClause = implode(',', $escapedVisibilities);

    // ✅ Fetch categories
    $catSql = "SELECT fc.id, fc.name, fc.visibility, COUNT(f.id) as article_count
               FROM faq_categories fc
               LEFT JOIN faq f ON fc.id = f.category
               WHERE fc.visibility IN ($inClause)
               GROUP BY fc.id, fc.name, fc.visibility
               ORDER BY fc.name ASC";
    $catResult = $adapter->createStatement($catSql)->execute();
    $categories = [];
    foreach ($catResult as $row) {
        $categories[] = $row;
    }

    // ✅ Fetch top articles
    $topSql = "SELECT f.id, f.question
               FROM faq f
               INNER JOIN faq_categories fc ON f.category = fc.id
               WHERE fc.visibility IN ($inClause)
               ORDER BY f.updated_at DESC
               LIMIT 5";
    $topResult = $adapter->createStatement($topSql)->execute();
    $topArticles = [];
    foreach ($topResult as $row) {
        $topArticles[] = $row;
    }
      // After fetching $faqs...

$voteSummarySql = "
  SELECT faq_id,
    SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) AS happy,
    SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) AS neutral,
    SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) AS sad,
    COUNT(*) AS total
  FROM tbl_faq_ratings
  GROUP BY faq_id
";

$voteResult = $adapter->createStatement($voteSummarySql)->execute();
$votes = [];
foreach ($voteResult as $row) {
    $votes[$row['faq_id']] = $row;
}


    return new ViewModel([
        'faqs'        => $faqs,
        'categories'  => $categories,
        'votes'       => $votes,
        'topArticles' => $topArticles
    ]);
}
public function searchAction()
{
    $request = $this->getRequest();
    $q = trim($this->params()->fromQuery('q', ''));

    // Empty query: blank result (frontend handle karega)
    if ($q === '') {
        return new \Zend\View\Model\JsonModel([
            'categories' => [],
            'articles'   => []
        ]);
    }

    $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');

    // ✅ Same visibility logic jo indexAction / categoriesAction me hai
    $userType = isset($_SESSION['user_type']) ? strtolower(trim($_SESSION['user_type'])) : '';
    $isAdmin  = (isset($_SESSION['STAFFUSER']) && $_SESSION['STAFFUSER'] == '1')
             || (isset($_SESSION['user_id']) && $_SESSION['user_id'] == '0');

    if ($isAdmin) {
        $allowedVisibilities = ['all', 'enterprise', 'admin'];
    } elseif ($userType === 'enterprise') {
        $allowedVisibilities = ['all', 'enterprise'];
    } else {
        $allowedVisibilities = ['all'];
    }

    $escapedVisibilities = array_map(function ($v) {
        return "'" . addslashes(strtolower($v)) . "'";
    }, $allowedVisibilities);
    $inClause = implode(',', $escapedVisibilities);

    // 🔹 1) Matching categories (topics)
    $catSql = "
        SELECT fc.id, fc.name, fc.visibility,
               COUNT(f.id) AS article_count
        FROM faq_categories fc
        LEFT JOIN faq f ON fc.id = f.category
        WHERE fc.visibility IN ($inClause)
          AND (LOWER(fc.name) LIKE ?)
        GROUP BY fc.id, fc.name, fc.visibility
        ORDER BY fc.name ASC
        LIMIT 20
    ";
    $catStmt = $adapter->createStatement($catSql);
    $catResult = $catStmt->execute(['%' . strtolower($q) . '%']);
    $categories = iterator_to_array($catResult);

    // 🔹 2) Matching articles (question / answer)
    $artSql = "
        SELECT f.id, f.question, f.category,
               fc.name AS category_name
        FROM faq f
        INNER JOIN faq_categories fc ON f.category = fc.id
        WHERE fc.visibility IN ($inClause)
          AND (
            LOWER(f.question) LIKE ?
            OR LOWER(f.answer) LIKE ?
          )
        ORDER BY f.updated_at DESC
        LIMIT 25
    ";
    $artStmt = $adapter->createStatement($artSql);
    $artResult = $artStmt->execute([
        '%' . strtolower($q) . '%',
        '%' . strtolower($q) . '%'
    ]);
    $articles = iterator_to_array($artResult);

    return new \Zend\View\Model\JsonModel([
        'categories' => $categories,
        'articles'   => $articles
    ]);
}

  public function rateAction()
{
    $request = $this->getRequest();

    if ($request->isPost()) {
        $data = json_decode($request->getContent(), true);
        $faqId = (int)($data['faq_id'] ?? 0);
        $rating = (int)($data['rating'] ?? 0);

        if ($faqId > 0 && in_array($rating, [1, 2, 3])) {
            $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
            $table = new \Zend\Db\TableGateway\TableGateway('tbl_faq_ratings', $adapter);

            $ip = $_SERVER['REMOTE_ADDR'] ?? null;
            $agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            $userId = $_SESSION['user_id'] ?? null;

            $table->insert([
                'faq_id'       => $faqId,
                'rating'       => $rating,
                'user_id'      => $userId,
                'ip_address'   => $ip,
                'user_agent'   => $agent,
                'submitted_at' => date('Y-m-d H:i:s')
            ]);

            return new \Zend\View\Model\JsonModel(['success' => true]);
        }
    }

    return new \Zend\View\Model\JsonModel(['success' => false]);
}


public function categoriesAction()
{
    $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');

    // Force lowercase session type (in case it’s "Enterprise")
    $userType = isset($_SESSION['user_type']) ? strtolower(trim($_SESSION['user_type'])) : '';
    $isAdmin = (isset($_SESSION['STAFFUSER']) && $_SESSION['STAFFUSER'] == '1') || (isset($_SESSION['user_id']) && $_SESSION['user_id'] == '0');

    // Determine allowed visibilities
    if ($isAdmin) {
        $allowedVisibilities = ['all', 'enterprise', 'admin'];
    } elseif ($userType === 'enterprise') {
        $allowedVisibilities = ['all', 'enterprise'];
    } else {
        $allowedVisibilities = ['all'];
    }

    // Build IN clause safely
    $escapedVisibilities = array_map(function ($v) {
        return "'" . addslashes(strtolower($v)) . "'";
    }, $allowedVisibilities);
    $inClause = implode(',', $escapedVisibilities);

    $sql = "SELECT fc.id, fc.name, fc.visibility, COUNT(f.id) as article_count
            FROM faq_categories fc
            LEFT JOIN faq f ON fc.id = f.category
            WHERE fc.visibility IN ($inClause)
            GROUP BY fc.id, fc.name, fc.visibility
            ORDER BY fc.name ASC";

    $result = $adapter->createStatement($sql)->execute();
    $categories = iterator_to_array($result);

    // Top 5 articles from visible categories
    $topSql = "SELECT f.id, f.question
               FROM faq f
               INNER JOIN faq_categories fc ON f.category = fc.id
               WHERE fc.visibility IN ($inClause)
               ORDER BY f.updated_at DESC
               LIMIT 5";

    $topResult = $adapter->createStatement($topSql)->execute();
    $topArticles = iterator_to_array($topResult);

    return new ViewModel([
        'categories'   => $categories,
        'topArticles'  => $topArticles
    ]);
}


public function categoryAction()
{
    $categoryId = (int) $this->params()->fromRoute('id', 0);
    $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');

    $catResult = $adapter->createStatement("SELECT name, visibility FROM faq_categories WHERE id = ?")
                         ->execute([$categoryId])
                         ->current();

    if (!$catResult) {
        return new ViewModel([
            'faqs' => [],
            'categoryId' => $categoryId,
            'categoryName' => 'Unknown',
            'accessDenied' => true,
            'categories' => []
        ]);
    }

    $visibility = strtolower(trim($catResult['visibility']));
    $userType   = isset($_SESSION['user_type']) ? strtolower(trim($_SESSION['user_type'])) : '';
    $isAdmin    = (isset($_SESSION['STAFFUSER']) && $_SESSION['STAFFUSER'] == '1') || (isset($_SESSION['user_id']) && $_SESSION['user_id'] == '0');

    // Validate access
    $allowed = $isAdmin || $visibility === 'all' || ($visibility === 'enterprise' && $userType === 'enterprise');

    if (!$allowed) {
        return new ViewModel([
            'faqs' => [],
            'categoryId' => $categoryId,
            'categoryName' => $catResult['name'],
            'accessDenied' => true,
            'categories' => []
        ]);
    }

    // ✅ Sidebar category list (based on access)
    $visibleTypes = ['all'];
    if ($userType === 'enterprise') $visibleTypes[] = 'enterprise';
    if ($isAdmin) $visibleTypes = ['all', 'enterprise', 'admin'];

    $inClause = "'" . implode("','", array_map('addslashes', $visibleTypes)) . "'";
    $catStmt = $adapter->createStatement(
        "SELECT fc.*, (SELECT COUNT(*) FROM faq f WHERE f.category = fc.id) AS article_count 
         FROM faq_categories fc 
         WHERE fc.visibility IN ($inClause)"
    );
    $categoryList = iterator_to_array($catStmt->execute());

    // Load articles from current category
    $faqStmt = $adapter->createStatement("SELECT * FROM faq WHERE category = ?");
    $faqs = iterator_to_array($faqStmt->execute([$categoryId]));

    return new ViewModel([
        'faqs' => $faqs,
        'categoryId' => $categoryId,
        'categoryName' => $catResult['name'],
        'accessDenied' => false,
        'categories' => $categoryList
    ]);
}



    public function addAction()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $category = trim($request->getPost('category_id'));
            $question = trim($request->getPost('title'));
            $answer = trim($request->getPost('answer'));

            if ($category && $question && $answer) {
                try {
                    $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
                    $sql = "INSERT INTO faq (category, question, answer) VALUES (?, ?, ?)";
                    $adapter->query($sql, [$category, $question, $answer]);

                    return $this->redirect()->toUrl('/faq/category/' . $category);
                } catch (\Exception $e) {
                    echo "Error: " . $e->getMessage();
                    exit;
                }
            } else {
                echo "Missing required fields";
                exit;
            }
        }

        return $this->redirect()->toUrl('/faq');
    }

    public function saveAction()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $id = (int) $request->getPost('id');
            $question = trim($request->getPost('question'));
            $answer = trim($request->getPost('answer'));

            if ($id && $question !== '' && $answer !== '') {
                try {
                    $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
                    $result = $adapter->createStatement("SELECT category FROM faq WHERE id = ?")->execute([$id])->current();
                    $categoryId = $result ? $result['category'] : null;

                    $sql = "UPDATE faq SET question = ?, answer = ?, updated_at = NOW() WHERE id = ?";
                    $adapter->query($sql, [$question, $answer, $id]);

                    if ($categoryId) {
                        return $this->redirect()->toUrl('/faq/category/' . $categoryId);
                    }
                } catch (\Exception $e) {
                    echo "Error: " . $e->getMessage();
                    exit;
                }
            } else {
                echo "Invalid data submitted.";
                exit;
            }
        }

        return $this->redirect()->toUrl('/faq');
    }

    public function deleteAction()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $id = (int) $request->getPost('id');
            $category = (int) $request->getPost('category');

            if ($id > 0) {
                try {
                    $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
                    $sql = "DELETE FROM faq WHERE id = ?";
                    $adapter->query($sql, [$id]);
                    return $this->redirect()->toUrl('/faq/category/' . $category);
                } catch (\Exception $e) {
                    echo "Error: " . $e->getMessage();
                    exit;
                }
            } else {
                echo "Invalid ID.";
                exit;
            }
        }

        return $this->redirect()->toUrl('/faq');
    }

    public function articleAction()
{
    $id = (int) $this->params()->fromRoute('id', 0);
    $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');

    $faq = $adapter->createStatement("SELECT * FROM faq WHERE id = ?")->execute([$id])->current();

    if (!$faq) {
        return $this->redirect()->toUrl('/faq');
    }

    // Get category name
    $catResult = $adapter->createStatement("SELECT name FROM faq_categories WHERE id = ?")
                         ->execute([$faq['category']])
                         ->current();
    $faq['category_name'] = $catResult ? $catResult['name'] : 'Unknown';

    // Get related articles (same category, exclude current ID)
    $relatedFaqs = [];
    $relatedStmt = $adapter->createStatement("SELECT id, question FROM faq WHERE category = ? AND id != ? LIMIT 5");
    foreach ($relatedStmt->execute([$faq['category'], $faq['id']]) as $row) {
        $relatedFaqs[] = $row;
    }
      // Get vote summary
$voteSql = "SELECT
    SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) AS happy,
    SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) AS neutral,
    SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) AS sad,
    COUNT(*) AS total
  FROM tbl_faq_ratings
  WHERE faq_id = ?";
$voteRow = $adapter->createStatement($voteSql)->execute([$faq['id']])->current();

    return new ViewModel([
        'faq' => $faq,
       'voteStats' => $voteRow,
        'relatedFaqs' => $relatedFaqs
    ]);
}

    public function addCategoryAction()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $name = trim($request->getPost('name'));

            if (!empty($name)) {
                try {
                    $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
                    $visibility = $request->getPost('visibility', 'all');
$sql = "INSERT INTO faq_categories (name, visibility) VALUES (?, ?)";
$adapter->query($sql, [$name, $visibility]);


                    return $this->redirect()->toUrl('/faq');
                } catch (\Exception $e) {
                    echo "Error: " . $e->getMessage();
                    exit;
                }
            } else {
                echo "Category name required.";
                exit;
            }
        }

        return $this->redirect()->toUrl('/faq');
    }

    public function deleteCategoryAction()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $id = (int) $request->getPost('id');

            if ($id > 0) {
                try {
                    $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
                    $sql = "DELETE FROM faq_categories WHERE id = ?";
                    $adapter->query($sql, [$id]);

                    return $this->redirect()->toUrl('/faq');
                } catch (\Exception $e) {
                    echo "Error deleting category: " . $e->getMessage();
                    exit;
                }
            } else {
                echo "Invalid category ID.";
                exit;
            }
        }

        return $this->redirect()->toUrl('/faq');
    }

    public function updateCategoryAction()
{
    $request = $this->getRequest();

    if ($request->isPost()) {
        $id = (int) $request->getPost('id');
        $name = trim($request->getPost('name'));
        $visibility = trim($request->getPost('visibility')); // 🔥 Add this

        if ($id > 0 && !empty($name) && in_array($visibility, ['all', 'enterprise', 'admin'])) {
            try {
                $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
                $sql = "UPDATE faq_categories SET name = ?, visibility = ? WHERE id = ?";
                $adapter->query($sql, [$name, $visibility, $id]);

                return $this->redirect()->toUrl('/faq');
            } catch (\Exception $e) {
                echo "Error updating category: " . $e->getMessage();
                exit;
            }
        } else {
            echo "Invalid input.";
            exit;
        }
    }

    return $this->redirect()->toUrl('/faq');
}

 }

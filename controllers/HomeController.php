<?php

class HomeController extends Controller {
    public function __construct() {
        parent::__construct();
        $this->loadModel('Book');
        $this->loadModel('Loan');
    }

    public function index() {
        // For now, we'll render the view without database data
        $latestBooks = [];  // We'll add real data later
        $popularBooks = []; // We'll add real data later
        
        $this->render('home/index', [
            'pageTitle' => 'Accueil - ' . APP_NAME,
            'latestBooks' => $latestBooks,
            'popularBooks' => $popularBooks
        ]);
    }

    public function search() {
        $query = $this->getQuery('q');
        $category = $this->getQuery('category');

        if (!empty($query)) {
            $sql = "SELECT b.*, c.name as category_name 
                FROM books b 
                LEFT JOIN categories c ON b.category_id = c.id 
                WHERE b.title LIKE :query 
                OR b.isbn LIKE :query";
            
            $params = [':query' => "%$query%"];

            if (!empty($category)) {
                $sql .= " AND b.category_id = :category";
                $params[':category'] = $category;
            }

            $results = $this->db->query($sql)
                ->bind(':query', "%$query%")
                ->bind(':category', $category)
                ->resultSet();

            if ($this->isAjax()) {
                $this->json(['success' => true, 'results' => $results]);
            } else {
                $this->render('home/search', [
                    'results' => $results,
                    'query' => $query,
                    'pageTitle' => 'Résultats de recherche - ' . APP_NAME
                ]);
            }
        } else {
            $this->redirect('');
        }
    }
}
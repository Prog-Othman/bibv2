<?php

class Book {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getBooks($page = 1, $perPage = 12, $category = null, $search = null) {
        $offset = ($page - 1) * $perPage;
        $params = [];
        
        $sql = "SELECT b.*, c.name as category_name, a.name as author_name, 
                (SELECT COUNT(*) = 0 FROM n_loan l WHERE l.book_id = b.id AND l.return_date IS NULL) as available 
                FROM n_livre b 
                LEFT JOIN n_categorie_livres c ON b.category_id = c.id 
                LEFT JOIN n_author a ON b.author_id = a.id 
                WHERE 1=1";

        if ($category) {
            $sql .= " AND b.category_id = ?";
            $params[] = $category;
        }

        if ($search) {
            $sql .= " AND (b.title LIKE ? OR b.isbn LIKE ? OR a.name LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " ORDER BY b.title ASC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        $this->db->query($sql);
        foreach ($params as $param) {
            $this->db->bind(null, $param);
        }

        return $this->db->resultSet();
    }

    public function getTotalBooks($category = null, $search = null) {
        $params = [];
        $sql = "SELECT COUNT(*) as total FROM n_livre b 
                LEFT JOIN n_author a ON b.author_id = a.id 
                WHERE 1=1";

        if ($category) {
            $sql .= " AND b.category_id = ?";
            $params[] = $category;
        }

        if ($search) {
            $sql .= " AND (b.title LIKE ? OR b.isbn LIKE ? OR a.name LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $this->db->query($sql);
        foreach ($params as $param) {
            $this->db->bind(null, $param);
        }

        $result = $this->db->single();
        return $result['total'];
    }

    public function getAllCategories() {
        $this->db->query("SELECT * FROM n_categorie_livres ORDER BY name ASC");
        return $this->db->resultSet();
    }

    public function getBookById($id) {
        $this->db->query("SELECT b.*, c.name as category_name, a.name as author_name 
                         FROM n_livre b 
                         LEFT JOIN n_categorie_livres c ON b.category_id = c.id 
                         LEFT JOIN n_author a ON b.author_id = a.id 
                         WHERE b.id = ?");
        $this->db->bind(null, $id);
        return $this->db->single();
    }

    public function isBookAvailable($id) {
        $this->db->query("SELECT COUNT(*) as loan_count 
                         FROM n_loan 
                         WHERE book_id = ? AND return_date IS NULL");
        $this->db->bind(null, $id);
        $result = $this->db->single();
        return $result['loan_count'] == 0;
    }

    public function getAll($limit = null, $offset = 0) {
        $sql = "SELECT l.*, cl.nom_categorie as category_name, a.author_name as authors 
            FROM n_livre l 
            LEFT JOIN n_categorie_livres cl ON l.id_categorie = cl.id_categorie 
            LEFT JOIN n_author a ON l.author_id = a.author_id 
            GROUP BY l.id_livre 
            ORDER BY l.titre";

        if ($limit !== null) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }

        $query = $this->db->query($sql);

        if ($limit !== null) {
            $query->bind(':limit', $limit, PDO::PARAM_INT)
                  ->bind(':offset', $offset, PDO::PARAM_INT);
        }

        return $query->resultSet();
    }

    public function getById($id) {
        return $this->db->query("SELECT l.*, cl.nom_categorie as category_name 
            FROM n_livre l 
            LEFT JOIN n_categorie_livres cl ON l.id_categorie = cl.id_categorie 
            WHERE l.id_livre = :id")
            ->bind(':id', $id)
            ->single();
    }

    public function getByIsbn($isbn) {
        return $this->db->query("SELECT * FROM n_livre WHERE isbn = :isbn")
            ->bind(':isbn', $isbn)
            ->single();
    }

    public function create($data) {
        try {
            $this->db->beginTransaction();
            // Adapter la création selon le nouveau schéma
            $sql = "INSERT INTO n_livre (titre, auteur, isbn, date_publication, id_categorie, quantite_totale) 
                   VALUES (:titre, :auteur, :isbn, :date_publication, :id_categorie, :quantite_totale)";
            $this->db->query($sql)
                ->bind(':titre', $data['titre'])
                ->bind(':auteur', $data['auteur'])
                ->bind(':isbn', $data['isbn'])
                ->bind(':date_publication', $data['date_publication'])
                ->bind(':id_categorie', $data['id_categorie'])
                ->bind(':quantite_totale', $data['quantite_totale'])
                ->execute();

            $this->db->query("INSERT INTO books (isbn, title, description, publisher, 
                publication_year, language, pages, category_id) 
                VALUES (:isbn, :title, :description, :publisher, :publication_year, 
                :language, :pages, :category_id)")
                ->bind(':isbn', $data['isbn'])
                ->bind(':title', $data['title'])
                ->bind(':description', $data['description'])
                ->bind(':publisher', $data['publisher'])
                ->bind(':publication_year', $data['publication_year'])
                ->bind(':language', $data['language'])
                ->bind(':pages', $data['pages'])
                ->bind(':category_id', $data['category_id'])
                ->execute();

            $bookId = $this->db->lastInsertId();

            // Ajout des auteurs
            if (!empty($data['authors'])) {
                foreach ($data['authors'] as $authorId) {
                    $this->db->query("INSERT INTO book_authors (book_id, author_id) 
                        VALUES (:book_id, :author_id)")
                        ->bind(':book_id', $bookId)
                        ->bind(':author_id', $authorId)
                        ->execute();
                }
            }

            $this->db->commit();
            return $bookId;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function update($id, $data) {
        try {
            $this->db->beginTransaction();

            $this->db->query("UPDATE books SET 
                isbn = :isbn,
                title = :title,
                description = :description,
                publisher = :publisher,
                publication_year = :publication_year,
                language = :language,
                pages = :pages,
                category_id = :category_id 
                WHERE id = :id")
                ->bind(':id', $id)
                ->bind(':isbn', $data['isbn'])
                ->bind(':title', $data['title'])
                ->bind(':description', $data['description'])
                ->bind(':publisher', $data['publisher'])
                ->bind(':publication_year', $data['publication_year'])
                ->bind(':language', $data['language'])
                ->bind(':pages', $data['pages'])
                ->bind(':category_id', $data['category_id'])
                ->execute();

            // Mise à jour des auteurs
            $this->db->query("DELETE FROM book_authors WHERE book_id = :book_id")
                ->bind(':book_id', $id)
                ->execute();

            if (!empty($data['authors'])) {
                foreach ($data['authors'] as $authorId) {
                    $this->db->query("INSERT INTO book_authors (book_id, author_id) 
                        VALUES (:book_id, :author_id)")
                        ->bind(':book_id', $id)
                        ->bind(':author_id', $authorId)
                        ->execute();
                }
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function delete($id) {
        return $this->db->query("DELETE FROM books WHERE id = :id")
            ->bind(':id', $id)
            ->execute();
    }

    public function search($query, $filters = []) {
        $sql = "SELECT b.*, c.name as category_name 
            FROM books b 
            LEFT JOIN categories c ON b.category_id = c.id 
            WHERE (b.title LIKE :query OR b.isbn LIKE :query)";

        $params = [':query' => "%$query%"];

        if (!empty($filters['category_id'])) {
            $sql .= " AND b.category_id = :category_id";
            $params[':category_id'] = $filters['category_id'];
        }

        if (!empty($filters['language'])) {
            $sql .= " AND b.language = :language";
            $params[':language'] = $filters['language'];
        }

        if (!empty($filters['year_from'])) {
            $sql .= " AND b.publication_year >= :year_from";
            $params[':year_from'] = $filters['year_from'];
        }

        if (!empty($filters['year_to'])) {
            $sql .= " AND b.publication_year <= :year_to";
            $params[':year_to'] = $filters['year_to'];
        }

        $query = $this->db->query($sql);
        foreach ($params as $param => $value) {
            $query->bind($param, $value);
        }

        return $query->resultSet();
    }

    public function getAvailableCopies($bookId) {
        return $this->db->query("SELECT * FROM book_copies 
            WHERE book_id = :book_id AND status = 'available'")
            ->bind(':book_id', $bookId)
            ->resultSet();
    }

    public function getTotalCopies($bookId) {
        return $this->db->query("SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available,
            SUM(CASE WHEN status = 'borrowed' THEN 1 ELSE 0 END) as borrowed,
            SUM(CASE WHEN status = 'maintenance' THEN 1 ELSE 0 END) as maintenance
            FROM book_copies 
            WHERE book_id = :book_id")
            ->bind(':book_id', $bookId)
            ->single();
    }
}
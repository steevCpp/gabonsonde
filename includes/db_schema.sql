-- PostgreSQL Database Schema for GabonSonde
-- Version: 1.0
-- Author: Agent

-- Enable pgcrypto extension for UUID generation if needed, though not used in this schema
-- CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- =============================================================================
-- FUNCTIONS AND TRIGGERS
-- =============================================================================

-- Function to update the updated_at timestamp
CREATE OR REPLACE FUNCTION trigger_set_timestamp()
RETURNS TRIGGER AS $$
BEGIN
  NEW.updated_at = NOW();
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- =============================================================================
-- TABLE DEFINITIONS
-- =============================================================================

-- -----------------------------------------------------
-- Table `users`
-- Stores information about users who might respond to surveys or register.
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
  id SERIAL PRIMARY KEY,
  email VARCHAR(255) UNIQUE, -- Can be NULL for anonymous or session-based users
  created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON TABLE users IS 'Stores user information. Email is unique if provided.';

-- -----------------------------------------------------
-- Table `surveys`
-- Stores information about each survey created.
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS surveys (
  id SERIAL PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  status VARCHAR(50) NOT NULL DEFAULT 'draft', -- e.g., 'draft', 'active', 'closed', 'archived'
  created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON TABLE surveys IS 'Main table for surveys.';
COMMENT ON COLUMN surveys.status IS 'Possible values: draft, active, closed, archived.';

-- Trigger for surveys updated_at
CREATE TRIGGER set_surveys_timestamp
BEFORE UPDATE ON surveys
FOR EACH ROW
EXECUTE FUNCTION trigger_set_timestamp();

-- -----------------------------------------------------
-- Table `questions`
-- Stores individual questions for each survey.
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS questions (
  id SERIAL PRIMARY KEY,
  survey_id INTEGER NOT NULL,
  question_text TEXT NOT NULL,
  question_type VARCHAR(50) NOT NULL, -- e.g., 'multiple_choice_single', 'multiple_choice_multiple', 'yes_no', 'open_text', 'rating_scale'
  question_order INTEGER NOT NULL DEFAULT 0,
  CONSTRAINT fk_questions_survey
    FOREIGN KEY (survey_id)
    REFERENCES surveys (id)
    ON DELETE CASCADE
);

COMMENT ON TABLE questions IS 'Stores questions related to surveys.';
COMMENT ON COLUMN questions.question_type IS 'Type of question, e.g., multiple_choice_single, yes_no, open_text.';

-- -----------------------------------------------------
-- Table `options`
-- Stores predefined answer options for multiple choice or rating scale questions.
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS options (
  id SERIAL PRIMARY KEY,
  question_id INTEGER NOT NULL,
  option_text VARCHAR(255) NOT NULL,
  option_value VARCHAR(100) NULL, -- Optional: value stored for the option (e.g., 1, 2, 'a', 'b')
  option_order INTEGER NOT NULL DEFAULT 0,
  CONSTRAINT fk_options_question
    FOREIGN KEY (question_id)
    REFERENCES questions (id)
    ON DELETE CASCADE
);

COMMENT ON TABLE options IS 'Stores answer choices for questions.';
COMMENT ON COLUMN options.option_value IS 'Actual value stored if different from option_text, e.g., for scales.';

-- -----------------------------------------------------
-- Table `responses`
-- Stores responses submitted by users for each question.
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS responses (
  id SERIAL PRIMARY KEY,
  survey_id INTEGER NOT NULL,
  question_id INTEGER NOT NULL,
  user_id INTEGER NULL, -- Can be NULL for anonymous responses
  option_id INTEGER NULL, -- For multiple_choice/rating_scale type answers
  response_text TEXT NULL, -- For open_text type answers
  submitted_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_responses_survey
    FOREIGN KEY (survey_id)
    REFERENCES surveys (id)
    ON DELETE CASCADE,
  CONSTRAINT fk_responses_question
    FOREIGN KEY (question_id)
    REFERENCES questions (id)
    ON DELETE CASCADE,
  CONSTRAINT fk_responses_user
    FOREIGN KEY (user_id)
    REFERENCES users (id)
    ON DELETE SET NULL,
  CONSTRAINT fk_responses_option
    FOREIGN KEY (option_id)
    REFERENCES options (id)
    ON DELETE SET NULL
);

COMMENT ON TABLE responses IS 'Stores user responses to survey questions.';
COMMENT ON COLUMN responses.user_id IS 'Null if response is anonymous.';
COMMENT ON COLUMN responses.option_id IS 'References an option if the question type uses options.';
COMMENT ON COLUMN responses.response_text IS 'Stores free-text answers.';

-- -----------------------------------------------------
-- Table `survey_orders`
-- Stores requests for custom surveys.
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS survey_orders (
  id SERIAL PRIMARY KEY,
  full_name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  phone VARCHAR(50),
  organization VARCHAR(255),
  survey_subject TEXT NOT NULL,
  target_audience TEXT,
  approx_questions INTEGER,
  objectives TEXT,
  budget VARCHAR(100),
  additional_info TEXT,
  status VARCHAR(50) DEFAULT 'pending', -- e.g., 'pending', 'contacted', 'in_progress', 'completed', 'cancelled'
  submitted_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON TABLE survey_orders IS 'Stores requests for custom-ordered surveys.';
COMMENT ON COLUMN survey_orders.status IS 'Tracks the status of the survey order.';


-- =============================================================================
-- SAMPLE DATA INSERTION
-- =============================================================================

-- -----------------------------------------------------
-- Sample User
-- -----------------------------------------------------
INSERT INTO users (email) VALUES
('exemple.utilisateur@email.com')
ON CONFLICT (email) DO NOTHING; -- Avoid error if user already exists

-- Get user_id for subsequent inserts (assuming this is the first user or a known one)
-- For robust scripting, you might want to query this ID. For simplicity here, we assume user_id = 1.
-- SELECT id into user_id_1 FROM users WHERE email = 'exemple.utilisateur@email.com';


-- -----------------------------------------------------
-- Sample Surveys
-- -----------------------------------------------------
INSERT INTO surveys (title, description, status) VALUES
('Opinion sur les services de transport public', 'Sondage visant à recueillir l''avis des usagers sur les services de transport en commun.', 'active'),
('Habitudes de consommation de médias', 'Enquête sur la manière dont les gens consomment différents types de médias.', 'draft');

-- Get survey_id for subsequent inserts
-- SELECT id into survey_id_1 FROM surveys WHERE title = 'Opinion sur les services de transport public';
-- SELECT id into survey_id_2 FROM surveys WHERE title = 'Habitudes de consommation de médias';

-- -----------------------------------------------------
-- Sample Questions for Survey 1 ("Opinion sur les services de transport public")
-- Assuming survey_id = 1 for this survey
-- -----------------------------------------------------
INSERT INTO questions (survey_id, question_text, question_type, question_order) VALUES
(1, 'Utilisez-vous les bus publics ?', 'yes_no', 1),
(1, 'Quelle est votre satisfaction concernant la fréquence des bus ?', 'rating_scale', 2),
(1, 'Suggérez une amélioration pour les transports publics :', 'open_text', 3);

-- Get question_ids for survey 1
-- SELECT id into q1_id_s1 FROM questions WHERE survey_id = 1 AND question_order = 1;
-- SELECT id into q2_id_s1 FROM questions WHERE survey_id = 1 AND question_order = 2;
-- SELECT id into q3_id_s1 FROM questions WHERE survey_id = 1 AND question_order = 3;

-- -----------------------------------------------------
-- Sample Options for Question 2 of Survey 1 ("Quelle est votre satisfaction...")
-- Assuming question_id for Q2 is 2 (if Q1 was 1)
-- -----------------------------------------------------
INSERT INTO options (question_id, option_text, option_value, option_order) VALUES
(2, 'Très insatisfait', '1', 1),
(2, 'Insatisfait', '2', 2),
(2, 'Neutre', '3', 3),
(2, 'Satisfait', '4', 4),
(2, 'Très satisfait', '5', 5);

-- -----------------------------------------------------
-- Sample Responses for Survey 1
-- Assuming user_id = 1, survey_id = 1
-- Question 1 (ID 1, yes_no): 'yes_no' responses are typically handled by application logic (e.g. storing 'yes'/'no' in response_text or using specific option_ids if modeled that way)
-- For this example, let's assume 'yes' is option_id NULL and response_text 'Oui' (or 'Non')
-- For a 'yes_no', one might create two options in the options table: (X, 'Oui', 'yes'), (X, 'Non', 'non')
-- Here, we'll use response_text for yes/no for simplicity as options were not explicitly defined for it.
-- -----------------------------------------------------
-- Response for Q1 (ID 1): User 1 answers "Oui"
INSERT INTO responses (survey_id, question_id, user_id, response_text) VALUES
(1, 1, 1, 'Oui');

-- Response for Q2 (ID 2): User 1 answers "Satisfait" (option_id for "Satisfait" is 4, assuming previous inserts: 1+1+3=5, so options start at 6.
-- To get actual option_id: SELECT id FROM options WHERE question_id = 2 AND option_value = '4'; -- Let's assume this returns 9
-- This part is tricky without dynamic ID fetching. For this static script, I'll assume IDs based on insertion order.
-- If options for Q2 are IDs 6,7,8,9,10. "Satisfait" (value 4) would be ID 9.
INSERT INTO responses (survey_id, question_id, user_id, option_id) VALUES
(1, 2, 1, (SELECT id FROM options WHERE question_id = 2 AND option_value = '4')); -- Dynamically get option_id

-- Response for Q3 (ID 3): User 1 gives an open text answer
INSERT INTO responses (survey_id, question_id, user_id, response_text) VALUES
(1, 3, 1, 'Augmenter le nombre de bus aux heures de pointe et améliorer la propreté.');

-- Another user's responses (anonymous, user_id IS NULL)
INSERT INTO responses (survey_id, question_id, response_text) VALUES
(1, 1, 'Non');
INSERT INTO responses (survey_id, question_id, option_id) VALUES
(1, 2, (SELECT id FROM options WHERE question_id = 2 AND option_value = '2')); -- "Insatisfait"
INSERT INTO responses (survey_id, question_id, response_text) VALUES
(1, 3, 'Les horaires devraient être plus fiables.');


-- -----------------------------------------------------
-- Sample Survey Order
-- -----------------------------------------------------
INSERT INTO survey_orders (full_name, email, phone, organization, survey_subject, target_audience, approx_questions, objectives, budget, additional_info, status) VALUES
(
  'Jean Dupont',
  'jean.dupont@example.com',
  '+241077777777',
  'Entreprise Gabonaise de Services',
  'Satisfaction des employés et bien-être au travail',
  'Tous les employés de l''entreprise (environ 250 personnes)',
  20,
  'Évaluer la satisfaction générale des employés, identifier les points forts et les points faibles de l''environnement de travail, recueillir des suggestions d''amélioration.',
  '1,500,000 FCFA',
  'Sondage anonyme. Résultats attendus sous 4 semaines après lancement. Souhaite un rapport détaillé avec analyse par département.',
  'pending'
);

-- =============================================================================
-- END OF SCHEMA AND SAMPLE DATA
-- =============================================================================
SELECT 'Database schema and sample data created successfully.' AS status;

CREATE TABLE migrations (
   migration TEXT PRIMARY KEY,
   created_at TIMESTAMP NOT NULL DEFAULT NOW()
);
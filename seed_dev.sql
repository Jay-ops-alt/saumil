-- Development-only seeds
INSERT INTO admin (username, password) VALUES
('admin', '$2y$10$lsRR.gYOeXKjMERzrnhwz.v2/iRCmN1fAfvluvsWR9nwTDHgy/ngS')
ON DUPLICATE KEY UPDATE username=VALUES(username);

INSERT INTO professors (name, email, password, status) VALUES
('Default Professor', 'professor@example.com', '$2y$10$r6Bf3.kjIEUvKN.bzIl9muuuP05bkTy9vJcaQBcn82rIxmM3o3sme', 'active')
ON DUPLICATE KEY UPDATE email=VALUES(email);

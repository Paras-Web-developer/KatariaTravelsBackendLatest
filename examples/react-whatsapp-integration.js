// React WhatsApp Integration Example
// This file shows how to integrate the WhatsApp API with a React frontend

import React, { useState, useEffect, useRef } from "react";
import axios from "axios";

// API Configuration
const API_BASE_URL = "https://api.katariatravel.ca/api";
const API_TOKEN = "your-auth-token"; // Get this from your authentication system

const api = axios.create({
	baseURL: API_BASE_URL,
	headers: {
		Authorization: `Bearer ${API_TOKEN}`,
		"Content-Type": "application/json",
	},
});

// WhatsApp Chat Component
const WhatsAppChat = () => {
	const [conversations, setConversations] = useState([]);
	const [selectedConversation, setSelectedConversation] = useState(null);
	const [messages, setMessages] = useState([]);
	const [newMessage, setNewMessage] = useState("");
	const [loading, setLoading] = useState(false);
	const [searchTerm, setSearchTerm] = useState("");
	const messagesEndRef = useRef(null);

	// Scroll to bottom of messages
	const scrollToBottom = () => {
		messagesEndRef.current?.scrollIntoView({ behavior: "smooth" });
	};

	useEffect(() => {
		scrollToBottom();
	}, [messages]);

	// Load conversations
	const loadConversations = async () => {
		try {
			setLoading(true);
			const response = await api.get("/whatsapp/conversations", {
				params: { search: searchTerm },
			});
			setConversations(response.data.data.conversations);
		} catch (error) {
			console.error("Error loading conversations:", error);
		} finally {
			setLoading(false);
		}
	};

	// Load messages for a conversation
	const loadMessages = async (conversationId) => {
		try {
			setLoading(true);
			const response = await api.get(
				`/whatsapp/conversations/${conversationId}/messages`
			);
			setMessages(response.data.data.messages);
			setSelectedConversation(response.data.data.conversation);
		} catch (error) {
			console.error("Error loading messages:", error);
		} finally {
			setLoading(false);
		}
	};

	// Send text message
	const sendTextMessage = async () => {
		if (!newMessage.trim() || !selectedConversation) return;

		try {
			setLoading(true);
			const response = await api.post("/whatsapp/messages/text", {
				phone_number: selectedConversation.phone_number,
				message: newMessage,
			});

			// Add message to local state
			setMessages((prev) => [...prev, response.data.data.message_record]);
			setNewMessage("");
		} catch (error) {
			console.error("Error sending message:", error);
		} finally {
			setLoading(false);
		}
	};

	// Send media message
	const sendMediaMessage = async (file, caption = "") => {
		if (!selectedConversation) return;

		try {
			setLoading(true);
			const formData = new FormData();
			formData.append("phone_number", selectedConversation.phone_number);
			formData.append("media", file);
			if (caption) formData.append("caption", caption);

			const response = await api.post(
				"/whatsapp/messages/media",
				formData,
				{
					headers: {
						"Content-Type": "multipart/form-data",
					},
				}
			);

			// Add message to local state
			setMessages((prev) => [...prev, response.data.data.message_record]);
		} catch (error) {
			console.error("Error sending media message:", error);
		} finally {
			setLoading(false);
		}
	};

	// Handle file upload
	const handleFileUpload = (event) => {
		const file = event.target.files[0];
		if (file) {
			const caption = prompt("Enter caption (optional):");
			sendMediaMessage(file, caption);
		}
	};

	// Mark conversation as read
	const markAsRead = async (conversationId) => {
		try {
			await api.post(
				`/whatsapp/conversations/${conversationId}/mark-read`
			);
		} catch (error) {
			console.error("Error marking as read:", error);
		}
	};

	// Toggle archive status
	const toggleArchive = async (conversationId) => {
		try {
			await api.post(
				`/whatsapp/conversations/${conversationId}/toggle-archive`
			);
			loadConversations(); // Reload conversations
		} catch (error) {
			console.error("Error toggling archive:", error);
		}
	};

	// Delete conversation
	const deleteConversation = async (conversationId) => {
		if (!confirm("Are you sure you want to delete this conversation?"))
			return;

		try {
			await api.delete(`/whatsapp/conversations/${conversationId}`);
			setSelectedConversation(null);
			setMessages([]);
			loadConversations(); // Reload conversations
		} catch (error) {
			console.error("Error deleting conversation:", error);
		}
	};

	// Load conversations on component mount
	useEffect(() => {
		loadConversations();
	}, [searchTerm]);

	return (
		<div className="whatsapp-chat-container">
			{/* Conversations Sidebar */}
			<div className="conversations-sidebar">
				<div className="search-container">
					<input
						type="text"
						placeholder="Search conversations..."
						value={searchTerm}
						onChange={(e) => setSearchTerm(e.target.value)}
						className="search-input"
					/>
				</div>

				<div className="conversations-list">
					{conversations.map((conversation) => (
						<div
							key={conversation.id}
							className={`conversation-item ${
								selectedConversation?.id === conversation.id
									? "active"
									: ""
							}`}
							onClick={() => {
								setSelectedConversation(conversation);
								loadMessages(conversation.id);
								markAsRead(conversation.id);
							}}
						>
							<div className="conversation-info">
								<div className="conversation-name">
									{conversation.name ||
										conversation.phone_number}
								</div>
								<div className="conversation-last-message">
									{conversation.last_message}
								</div>
								<div className="conversation-time">
									{new Date(
										conversation.last_message_at
									).toLocaleTimeString()}
								</div>
							</div>
							{conversation.unread_count > 0 && (
								<div className="unread-badge">
									{conversation.unread_count}
								</div>
							)}
						</div>
					))}
				</div>
			</div>

			{/* Chat Area */}
			<div className="chat-area">
				{selectedConversation ? (
					<>
						{/* Chat Header */}
						<div className="chat-header">
							<div className="chat-contact-info">
								<div className="contact-name">
									{selectedConversation.name ||
										selectedConversation.phone_number}
								</div>
								<div className="contact-status">Online</div>
							</div>
							<div className="chat-actions">
								<button
									onClick={() =>
										toggleArchive(selectedConversation.id)
									}
								>
									{selectedConversation.is_archived
										? "Unarchive"
										: "Archive"}
								</button>
								<button
									onClick={() =>
										deleteConversation(
											selectedConversation.id
										)
									}
								>
									Delete
								</button>
							</div>
						</div>

						{/* Messages Area */}
						<div className="messages-area">
							{messages.map((message) => (
								<div
									key={message.id}
									className={`message ${
										message.direction === "outbound"
											? "sent"
											: "received"
									}`}
								>
									<div className="message-content">
										{message.type === "text" && (
											<div className="message-text">
												{message.content}
											</div>
										)}
										{message.is_media && (
											<div className="message-media">
												{message.type === "image" && (
													<img
														src={message.file_url}
														alt="Media"
													/>
												)}
												{message.type === "video" && (
													<video
														controls
														src={message.file_url}
													/>
												)}
												{message.type ===
													"document" && (
													<a
														href={message.file_url}
														target="_blank"
														rel="noopener noreferrer"
													>
														📎 {message.file_name}
													</a>
												)}
												{message.content && (
													<div className="message-caption">
														{message.content}
													</div>
												)}
											</div>
										)}
										<div className="message-time">
											{new Date(
												message.created_at
											).toLocaleTimeString()}
										</div>
									</div>
								</div>
							))}
							<div ref={messagesEndRef} />
						</div>

						{/* Message Input */}
						<div className="message-input-container">
							<input
								type="text"
								placeholder="Type a message..."
								value={newMessage}
								onChange={(e) => setNewMessage(e.target.value)}
								onKeyPress={(e) =>
									e.key === "Enter" && sendTextMessage()
								}
								className="message-input"
								disabled={loading}
							/>
							<input
								type="file"
								id="media-upload"
								accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.txt"
								onChange={handleFileUpload}
								style={{ display: "none" }}
							/>
							<label
								htmlFor="media-upload"
								className="media-upload-btn"
							>
								📎
							</label>
							<button
								onClick={sendTextMessage}
								disabled={loading || !newMessage.trim()}
								className="send-btn"
							>
								{loading ? "Sending..." : "Send"}
							</button>
						</div>
					</>
				) : (
					<div className="no-conversation-selected">
						Select a conversation to start chatting
					</div>
				)}
			</div>
		</div>
	);
};

// Bulk Message Component
const BulkMessageSender = () => {
	const [phoneNumbers, setPhoneNumbers] = useState("");
	const [message, setMessage] = useState("");
	const [mediaFile, setMediaFile] = useState(null);
	const [loading, setLoading] = useState(false);
	const [results, setResults] = useState([]);

	const sendBulkMessage = async () => {
		if (!phoneNumbers.trim() || !message.trim()) return;

		try {
			setLoading(true);
			const phoneNumbersArray = phoneNumbers
				.split(",")
				.map((num) => num.trim());

			const formData = new FormData();
			phoneNumbersArray.forEach((num) =>
				formData.append("phone_numbers[]", num)
			);
			formData.append("message", message);
			if (mediaFile) {
				formData.append("media", mediaFile);
			}

			const response = await api.post(
				"/whatsapp/messages/bulk",
				formData,
				{
					headers: {
						"Content-Type": "multipart/form-data",
					},
				}
			);

			setResults(response.data.data.results);
		} catch (error) {
			console.error("Error sending bulk message:", error);
		} finally {
			setLoading(false);
		}
	};

	return (
		<div className="bulk-message-sender">
			<h3>Send Bulk Message</h3>

			<div className="form-group">
				<label>Phone Numbers (comma-separated):</label>
				<textarea
					value={phoneNumbers}
					onChange={(e) => setPhoneNumbers(e.target.value)}
					placeholder="+1234567890, +0987654321, ..."
					rows={3}
				/>
			</div>

			<div className="form-group">
				<label>Message:</label>
				<textarea
					value={message}
					onChange={(e) => setMessage(e.target.value)}
					placeholder="Enter your message..."
					rows={4}
				/>
			</div>

			<div className="form-group">
				<label>Media (optional):</label>
				<input
					type="file"
					onChange={(e) => setMediaFile(e.target.files[0])}
					accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.txt"
				/>
			</div>

			<button
				onClick={sendBulkMessage}
				disabled={loading || !phoneNumbers.trim() || !message.trim()}
				className="send-bulk-btn"
			>
				{loading ? "Sending..." : "Send Bulk Message"}
			</button>

			{results.length > 0 && (
				<div className="results">
					<h4>Results:</h4>
					{results.map((result, index) => (
						<div
							key={index}
							className={`result ${
								result.success ? "success" : "error"
							}`}
						>
							{result.phone_number}:{" "}
							{result.success ? "Sent" : result.error}
						</div>
					))}
				</div>
			)}
		</div>
	);
};

// Statistics Component
const WhatsAppStatistics = () => {
	const [statistics, setStatistics] = useState(null);
	const [loading, setLoading] = useState(false);

	const loadStatistics = async () => {
		try {
			setLoading(true);
			const response = await api.get("/whatsapp/statistics");
			setStatistics(response.data.data);
		} catch (error) {
			console.error("Error loading statistics:", error);
		} finally {
			setLoading(false);
		}
	};

	useEffect(() => {
		loadStatistics();
	}, []);

	if (loading) return <div>Loading statistics...</div>;
	if (!statistics) return null;

	return (
		<div className="statistics">
			<h3>WhatsApp Statistics</h3>
			<div className="stats-grid">
				<div className="stat-item">
					<div className="stat-value">
						{statistics.total_conversations}
					</div>
					<div className="stat-label">Total Conversations</div>
				</div>
				<div className="stat-item">
					<div className="stat-value">
						{statistics.total_messages}
					</div>
					<div className="stat-label">Total Messages</div>
				</div>
				<div className="stat-item">
					<div className="stat-value">
						{statistics.unread_messages}
					</div>
					<div className="stat-label">Unread Messages</div>
				</div>
				<div className="stat-item">
					<div className="stat-value">
						{statistics.today_messages}
					</div>
					<div className="stat-label">Today's Messages</div>
				</div>
			</div>
		</div>
	);
};

// Main App Component
const WhatsAppApp = () => {
	const [activeTab, setActiveTab] = useState("chat");

	return (
		<div className="whatsapp-app">
			<div className="app-header">
				<h1>WhatsApp Integration</h1>
				<div className="tab-navigation">
					<button
						className={activeTab === "chat" ? "active" : ""}
						onClick={() => setActiveTab("chat")}
					>
						Chat
					</button>
					<button
						className={activeTab === "bulk" ? "active" : ""}
						onClick={() => setActiveTab("bulk")}
					>
						Bulk Messages
					</button>
					<button
						className={activeTab === "stats" ? "active" : ""}
						onClick={() => setActiveTab("stats")}
					>
						Statistics
					</button>
				</div>
			</div>

			<div className="app-content">
				{activeTab === "chat" && <WhatsAppChat />}
				{activeTab === "bulk" && <BulkMessageSender />}
				{activeTab === "stats" && <WhatsAppStatistics />}
			</div>
		</div>
	);
};

export default WhatsAppApp;

// CSS Styles (add to your stylesheet)
/*
.whatsapp-app {
  display: flex;
  flex-direction: column;
  height: 100vh;
}

.app-header {
  background: #075e54;
  color: white;
  padding: 1rem;
}

.tab-navigation {
  display: flex;
  gap: 1rem;
  margin-top: 1rem;
}

.tab-navigation button {
  background: transparent;
  border: 1px solid white;
  color: white;
  padding: 0.5rem 1rem;
  cursor: pointer;
}

.tab-navigation button.active {
  background: white;
  color: #075e54;
}

.whatsapp-chat-container {
  display: flex;
  height: calc(100vh - 100px);
}

.conversations-sidebar {
  width: 300px;
  border-right: 1px solid #ddd;
  display: flex;
  flex-direction: column;
}

.search-container {
  padding: 1rem;
  border-bottom: 1px solid #ddd;
}

.search-input {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.conversations-list {
  flex: 1;
  overflow-y: auto;
}

.conversation-item {
  padding: 1rem;
  border-bottom: 1px solid #eee;
  cursor: pointer;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.conversation-item:hover {
  background: #f5f5f5;
}

.conversation-item.active {
  background: #e3f2fd;
}

.chat-area {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.chat-header {
  padding: 1rem;
  border-bottom: 1px solid #ddd;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.messages-area {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.message {
  display: flex;
  margin-bottom: 1rem;
}

.message.sent {
  justify-content: flex-end;
}

.message.received {
  justify-content: flex-start;
}

.message-content {
  max-width: 70%;
  padding: 0.5rem 1rem;
  border-radius: 1rem;
}

.message.sent .message-content {
  background: #dcf8c6;
}

.message.received .message-content {
  background: white;
  border: 1px solid #ddd;
}

.message-input-container {
  padding: 1rem;
  border-top: 1px solid #ddd;
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.message-input {
  flex: 1;
  padding: 0.5rem;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.send-btn, .media-upload-btn {
  padding: 0.5rem 1rem;
  background: #075e54;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.send-btn:disabled {
  background: #ccc;
  cursor: not-allowed;
}

.bulk-message-sender, .statistics {
  padding: 2rem;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: bold;
}

.form-group textarea, .form-group input {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.send-bulk-btn {
  padding: 0.5rem 1rem;
  background: #075e54;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-top: 1rem;
}

.stat-item {
  text-align: center;
  padding: 1rem;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.stat-value {
  font-size: 2rem;
  font-weight: bold;
  color: #075e54;
}

.stat-label {
  margin-top: 0.5rem;
  color: #666;
}

.results {
  margin-top: 1rem;
  padding: 1rem;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.result {
  padding: 0.5rem;
  margin-bottom: 0.5rem;
  border-radius: 4px;
}

.result.success {
  background: #d4edda;
  color: #155724;
}

.result.error {
  background: #f8d7da;
  color: #721c24;
}

.no-conversation-selected {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  color: #666;
  font-size: 1.2rem;
}
*/

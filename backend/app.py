from flask import Flask, request, jsonify
from flask_sqlalchemy import SQLAlchemy
from flask_cors import CORS
from datetime import datetime
import os

app = Flask(__name__)
CORS(app)

# Database Configuration
basedir = os.path.abspath(os.path.dirname(__file__))
app.config['SQLALCHEMY_DATABASE_URI'] = 'sqlite:///' + os.path.join(basedir, 'chama.db')
app.config['SQLALCHEMY_TRACK_MODIFICATIONS'] = False

db = SQLAlchemy(app)

# Models
class Group(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    name = db.Column(db.String(100), nullable=False)
    description = db.Column(db.String(200))
    created_at = db.Column(db.DateTime, default=datetime.utcnow)
    members = db.relationship('Member', backref='group', lazy=True)
    transactions = db.relationship('Transaction', backref='group', lazy=True)

    def to_dict(self):
        return {
            'id': self.id,
            'name': self.name,
            'description': self.description,
            'created_at': self.created_at.isoformat(),
            'member_count': len(self.members),
            'balance': sum(t.amount for t in self.transactions)
        }

class Member(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    name = db.Column(db.String(100), nullable=False)
    phone = db.Column(db.String(20), nullable=False)
    group_id = db.Column(db.Integer, db.ForeignKey('group.id'), nullable=False)
    joined_at = db.Column(db.DateTime, default=datetime.utcnow)
    transactions = db.relationship('Transaction', backref='member', lazy=True)

    def to_dict(self):
        return {
            'id': self.id,
            'name': self.name,
            'phone': self.phone,
            'group_id': self.group_id,
            'joined_at': self.joined_at.isoformat(),
            'total_contribution': sum(t.amount for t in self.transactions if t.amount > 0)
        }

class Transaction(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    amount = db.Column(db.Float, nullable=False) # Positive for contribution, Negative for withdrawal/expense
    description = db.Column(db.String(200))
    date = db.Column(db.DateTime, default=datetime.utcnow)
    group_id = db.Column(db.Integer, db.ForeignKey('group.id'), nullable=False)
    member_id = db.Column(db.Integer, db.ForeignKey('member.id'), nullable=True) # Nullable for group-wide expenses

    def to_dict(self):
        return {
            'id': self.id,
            'amount': self.amount,
            'description': self.description,
            'date': self.date.isoformat(),
            'group_id': self.group_id,
            'member_id': self.member_id,
            'member_name': self.member.name if self.member else 'Group Expense'
        }

# Routes
@app.route('/api/groups', methods=['GET'])
def get_groups():
    groups = Group.query.all()
    return jsonify([g.to_dict() for g in groups])

@app.route('/api/groups', methods=['POST'])
def create_group():
    data = request.json
    new_group = Group(name=data['name'], description=data.get('description', ''))
    db.session.add(new_group)
    db.session.commit()
    return jsonify(new_group.to_dict()), 201

# Initialize DB
with app.app_context():
    db.create_all()

@app.route('/api/groups/<int:group_id>/members', methods=['GET'])
def get_members(group_id):
    members = Member.query.filter_by(group_id=group_id).all()
    return jsonify([m.to_dict() for m in members])

@app.route('/api/members', methods=['POST'])
def add_member():
    data = request.json
    new_member = Member(
        name=data['name'],
        phone=data['phone'],
        group_id=data['group_id']
    )
    db.session.add(new_member)
    db.session.commit()
    return jsonify(new_member.to_dict()), 201

@app.route('/api/contributions', methods=['POST'])
def add_contribution():
    data = request.json
    # 1. Create Transaction
    amount = float(data['amount'])
    new_transaction = Transaction(
        amount=amount,
        description=data.get('description', 'Contribution'),
        group_id=data['group_id'],
        member_id=data['member_id']
    )
    db.session.add(new_transaction)
    db.session.commit()
    return jsonify(new_transaction.to_dict()), 201

@app.route('/api/groups/<int:group_id>/transactions', methods=['GET'])
def get_transactions(group_id):
    transactions = Transaction.query.filter_by(group_id=group_id).order_by(Transaction.date.desc()).all()
    return jsonify([t.to_dict() for t in transactions])

@app.route('/api/groups/<int:group_id>/balance', methods=['GET'])
def get_balance(group_id):
    group = Group.query.get_or_404(group_id)
    balance = sum(t.amount for t in group.transactions)
    return jsonify({'group_id': group.id, 'balance': balance})

# M-Pesa Integration
from mpesa import MpesaClient
mpesa = MpesaClient()

@app.route('/api/mpesa/stkpush', methods=['POST'])
def mpesa_stk_push():
    data = request.json
    phone = data.get('phone')
    amount = data.get('amount')
    group_id = data.get('group_id')
    member_id = data.get('member_id')
    
    # In a real app, we would save a pending transaction here
    
    response = mpesa.stk_push(
        phone_number=phone,
        amount=amount,
        account_reference=f"Group{group_id}",
        transaction_desc=f"Contribution for Member {member_id}"
    )
    
    return jsonify(response)

@app.route('/api/mpesa/callback', methods=['POST'])
def mpesa_callback():
    data = request.json
    # Handle callback data here (e.g. verify payment and update DB)
    print("M-Pesa Callback:", data)
    return "OK"

if __name__ == '__main__':
    app.run(debug=True, port=5000)

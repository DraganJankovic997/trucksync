export default {
  title: 'Approval page',
  eyebrow: 'Admin controls',
  actions: {
    refresh: 'Refresh'
  },
  table: {
    actions: 'Actions',
    approve: 'Approve',
    block: 'Block',
    approveAria: 'Approve {name}',
    blockAria: 'Block {name}',
    profileId: 'Profile ID',
    userId: 'User ID',
    firstName: 'First name',
    lastName: 'Last name',
    email: 'Email',
    phoneNumber: 'Phone',
    country: 'Country',
    city: 'City',
    address: 'Address',
    postCode: 'Post code',
    status: 'Status',
    pending: 'Pending',
    approved: 'Approved',
    emptyValue: '-'
  },
  dispatchers: {
    title: 'Dispatchers',
    count: 'Pending dispatchers: {count}',
    companyName: 'Company',
    registrationNumber: 'Registration number',
    emptyTitle: 'No dispatcher approvals',
    emptyDescription:
      'Dispatcher profiles waiting for approval will appear here.'
  },
  restStops: {
    title: 'Rest stops',
    count: 'Pending rest stops: {count}',
    worksFrom: 'Works from',
    worksTo: 'Works to',
    emptyTitle: 'No rest stop approvals',
    emptyDescription:
      'Rest stop profiles waiting for approval will appear here.'
  }
};
